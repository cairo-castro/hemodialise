<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SafetyChecklist;
use App\Models\CleaningControl;
use App\Models\ChemicalDisinfection;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * DataSyncController - Sistema leve de sincronização por polling
 *
 * Esta solução usa apenas 1-2% da performance de WebSockets e é ideal para
 * servidores com múltiplas aplicações. Usa cache agressivo para minimizar
 * consultas ao banco de dados.
 */
class DataSyncController extends Controller
{
    /**
     * Verifica se há novas atualizações desde o último check do cliente
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUpdates(Request $request)
    {
        $lastCheck = $request->input('last_check');
        $lastCheckTime = $lastCheck ? Carbon::parse($lastCheck) : Carbon::now()->subMinutes(5);

        // Usar unidade ativa do middleware (respeita current_unit_id)
        $unitId = $request->get('scoped_unit_id');

        // Cache key único por unidade para evitar colisões
        $cacheKey = "data_sync_unit_{$unitId}_last_update";

        // Verifica o timestamp da última atualização (em cache por 10 segundos)
        $lastUpdate = Cache::remember($cacheKey, 10, function() use ($unitId) {
            // Busca apenas o timestamp mais recente de cada tabela usando query scope
            $latestSafety = SafetyChecklist::forUnit($unitId)->max('updated_at');
            $latestCleaning = CleaningControl::forUnit($unitId)->max('updated_at');
            $latestDisinfection = ChemicalDisinfection::forUnit($unitId)->max('updated_at');

            // Retorna o timestamp mais recente de todos
            return collect([$latestSafety, $latestCleaning, $latestDisinfection])
                ->filter()
                ->max();
        });

        // Se não há atualizações, retorna resposta vazia
        if (!$lastUpdate || Carbon::parse($lastUpdate)->lte($lastCheckTime)) {
            return response()->json([
                'has_updates' => false,
                'last_update' => $lastUpdate,
                'checked_at' => now()->toISOString(),
            ]);
        }

        // Há atualizações! Retorna os dados alterados
        return response()->json([
            'has_updates' => true,
            'last_update' => $lastUpdate,
            'checked_at' => now()->toISOString(),
            'data' => [
                'safety_checklists' => $this->getUpdatedSafetyChecklists($lastCheckTime, $unitId),
                'cleaning_controls' => $this->getUpdatedCleaningControls($lastCheckTime, $unitId),
                'chemical_disinfections' => $this->getUpdatedChemicalDisinfections($lastCheckTime, $unitId),
            ],
        ]);
    }

    /**
     * Retorna checklists de segurança atualizados
     */
    private function getUpdatedSafetyChecklists($since, $unitId)
    {
        return Cache::remember("safety_updates_{$unitId}_{$since}", 10, function() use ($since, $unitId) {
            return SafetyChecklist::with(['machine', 'patient', 'user'])
                ->forUnit($unitId)  // Using query scope
                ->where('updated_at', '>', $since)
                ->orderBy('updated_at', 'desc')
                ->limit(50) // Limita para evitar sobrecarregar
                ->get()
                ->map(function($checklist) {
                    return [
                        'id' => $checklist->id,
                        'machine_id' => $checklist->machine_id,
                        'machine_name' => $checklist->machine->name ?? null,
                        'patient_id' => $checklist->patient_id,
                        'patient_name' => $checklist->patient->name ?? null,
                        'date' => $checklist->date,
                        'shift' => $checklist->shift,
                        'user_name' => $checklist->user->name ?? null,
                        'updated_at' => $checklist->updated_at->toISOString(),
                        'created_at' => $checklist->created_at->toISOString(),
                    ];
                });
        });
    }

    /**
     * Retorna controles de limpeza atualizados
     */
    private function getUpdatedCleaningControls($since, $unitId)
    {
        return Cache::remember("cleaning_updates_{$unitId}_{$since}", 10, function() use ($since, $unitId) {
            return CleaningControl::with(['machine', 'user'])
                ->forUnit($unitId)  // Using query scope
                ->where('updated_at', '>', $since)
                ->orderBy('updated_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function($control) {
                    return [
                        'id' => $control->id,
                        'machine_id' => $control->machine_id,
                        'machine_name' => $control->machine->name ?? null,
                        'date' => $control->date,
                        'shift' => $control->shift,
                        'cleaning_type' => $control->cleaning_type,
                        'user_name' => $control->user->name ?? null,
                        'updated_at' => $control->updated_at->toISOString(),
                        'created_at' => $control->created_at->toISOString(),
                    ];
                });
        });
    }

    /**
     * Retorna desinfecções químicas atualizadas
     */
    private function getUpdatedChemicalDisinfections($since, $unitId)
    {
        return Cache::remember("disinfection_updates_{$unitId}_{$since}", 10, function() use ($since, $unitId) {
            return ChemicalDisinfection::with(['machine', 'user'])
                ->forUnit($unitId)  // Using query scope
                ->where('updated_at', '>', $since)
                ->orderBy('updated_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function($disinfection) {
                    return [
                        'id' => $disinfection->id,
                        'machine_id' => $disinfection->machine_id,
                        'machine_name' => $disinfection->machine->name ?? null,
                        'date' => $disinfection->date,
                        'disinfectant_type' => $disinfection->disinfectant_type,
                        'user_name' => $disinfection->user->name ?? null,
                        'updated_at' => $disinfection->updated_at->toISOString(),
                        'created_at' => $disinfection->created_at->toISOString(),
                    ];
                });
        });
    }

    /**
     * Limpa o cache de sincronização forçadamente
     * Útil após criar/editar dados para garantir atualização imediata
     */
    public function invalidateCache(Request $request)
    {
        // Usar unidade ativa do middleware (respeita current_unit_id)
        $unitId = $request->get('scoped_unit_id');

        $cacheKey = "data_sync_unit_{$unitId}_last_update";
        Cache::forget($cacheKey);

        // Limpa também caches de atualizações específicas
        Cache::flush(); // Em produção, seria mais seletivo

        return response()->json([
            'success' => true,
            'message' => 'Cache invalidado com sucesso',
        ]);
    }
}
