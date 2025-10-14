<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\JsonResponse;

class MachineController extends Controller
{
    public function available(): JsonResponse
    {
        try {
            $query = Machine::available()->orderBy('name');
            
            // Aplicar filtro de unidade
            $scopedUnitId = request()->get('scoped_unit_id');
            if ($scopedUnitId !== null) {
                $query->where('unit_id', $scopedUnitId);
            }

            $machines = $query->get(['id', 'name', 'identifier', 'description', 'status', 'unit_id']);

            return response()->json([
                'success' => true,
                'machines' => $machines,
                'total' => $machines->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar máquinas disponíveis.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(): JsonResponse
    {
        try {
            $query = Machine::orderBy('name');
            
            // Aplicar filtro de unidade
            $scopedUnitId = request()->get('scoped_unit_id');
            if ($scopedUnitId !== null) {
                $query->where('unit_id', $scopedUnitId);
            }

            $machines = $query->get();

            // Adicionar status info para cada máquina
            $machines = $machines->map(function ($machine) {
                $currentChecklist = $machine->getCurrentChecklist();

                $checklistData = null;
                if ($currentChecklist) {
                    // Load patient relationship if exists
                    $currentChecklist->load('patient');

                    $checklistData = [
                        'id' => $currentChecklist->id,
                        'current_phase' => $currentChecklist->current_phase,
                        'patient_name' => $currentChecklist->patient->full_name ?? null,
                        'started_at' => $currentChecklist->created_at,
                        'is_paused' => method_exists($currentChecklist, 'isPaused') ? $currentChecklist->isPaused() : false,
                    ];
                }

                return [
                    'id' => $machine->id,
                    'name' => $machine->name,
                    'identifier' => $machine->identifier,
                    'description' => $machine->description,
                    'status' => $machine->status,
                    'is_active' => $machine->active,
                    'is_available' => $machine->isAvailable(),
                    'is_occupied' => $machine->isOccupied(),
                    'is_reserved' => $machine->isReserved(),
                    'current_checklist' => $checklistData
                ];
            });

            return response()->json([
                'success' => true,
                'machines' => $machines,
                'total' => $machines->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar máquinas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna estatísticas de disponibilidade de máquinas
     */
    public function availability(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $query = Machine::where('active', true);

            // Aplicar filtro de unidade
            $scopedUnitId = $request->get('scoped_unit_id');
            if ($scopedUnitId !== null) {
                $query->where('unit_id', $scopedUnitId);
            }

            $total = $query->count();
            $available = (clone $query)->where('status', 'available')->count();
            $occupied = (clone $query)->where('status', 'occupied')->count();
            $reserved = (clone $query)->where('status', 'reserved')->count();
            $maintenance = (clone $query)->where('status', 'maintenance')->count();

            // Calcular percentuais
            $availablePercent = $total > 0 ? round(($available / $total) * 100, 1) : 0;
            $occupiedPercent = $total > 0 ? round(($occupied / $total) * 100, 1) : 0;

            // Determinar status geral
            $overallStatus = 'good'; // Verde
            $message = 'Máquinas disponíveis para uso';

            if ($available === 0) {
                $overallStatus = 'critical'; // Vermelho
                $message = 'Nenhuma máquina disponível no momento';
            } elseif ($availablePercent < 20) {
                $overallStatus = 'warning'; // Amarelo
                $message = 'Poucas máquinas disponíveis';
            } elseif ($availablePercent < 50) {
                $overallStatus = 'alert'; // Laranja
                $message = 'Disponibilidade moderada de máquinas';
            }

            return response()->json([
                'success' => true,
                'availability' => [
                    'total' => $total,
                    'available' => $available,
                    'occupied' => $occupied,
                    'reserved' => $reserved,
                    'maintenance' => $maintenance,
                    'available_percent' => $availablePercent,
                    'occupied_percent' => $occupiedPercent,
                    'overall_status' => $overallStatus,
                    'message' => $message,
                    'can_create_checklist' => $available > 0
                ],
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar disponibilidade de máquinas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alterar status da máquina (available, maintenance)
     */
    public function updateStatus(\Illuminate\Http\Request $request, Machine $machine): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:available,maintenance',
                'reason' => 'nullable|string|max:500'
            ]);

            $newStatus = $request->input('status');
            $reason = $request->input('reason');

            // Validar se a máquina está ativa
            if (!$machine->active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Máquina desativada não pode ter o status alterado. Ative a máquina primeiro.'
                ], 422);
            }

            // Validar se máquina está ocupada
            if ($machine->status === 'occupied') {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível alterar o status de uma máquina ocupada. Finalize a sessão primeiro.'
                ], 422);
            }

            // Validar se máquina está reservada
            if ($machine->status === 'reserved') {
                $currentChecklist = $machine->getCurrentChecklist();
                if ($currentChecklist) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Máquina reservada para um checklist. Cancele o checklist primeiro.'
                    ], 422);
                }
            }

            // Atualizar status
            $oldStatus = $machine->status;
            $machine->status = $newStatus;
            $machine->save();

            // Log da alteração (opcional - você pode criar uma tabela de logs)
            \Log::info("Status da máquina alterado", [
                'machine_id' => $machine->id,
                'machine_name' => $machine->name,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $reason,
                'user_id' => $request->user()->id,
                'user_name' => $request->user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => $newStatus === 'maintenance' 
                    ? 'Máquina colocada em manutenção com sucesso.'
                    : 'Máquina marcada como disponível com sucesso.',
                'machine' => [
                    'id' => $machine->id,
                    'name' => $machine->name,
                    'status' => $machine->status,
                    'is_active' => $machine->active
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status da máquina.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ativar/Desativar máquina
     */
    public function toggleActive(\Illuminate\Http\Request $request, Machine $machine): JsonResponse
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:500'
            ]);

            $reason = $request->input('reason');

            // Se está ativando (estava desativada)
            if (!$machine->active) {
                $machine->active = true;
                $machine->status = 'available'; // Volta como disponível
                $machine->save();

                \Log::info("Máquina ativada", [
                    'machine_id' => $machine->id,
                    'machine_name' => $machine->name,
                    'reason' => $reason,
                    'user_id' => $request->user()->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Máquina ativada com sucesso.',
                    'machine' => [
                        'id' => $machine->id,
                        'name' => $machine->name,
                        'status' => $machine->status,
                        'is_active' => $machine->active
                    ]
                ]);
            }

            // Se está desativando (estava ativa)
            // Validar se máquina está ocupada
            if ($machine->status === 'occupied') {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível desativar uma máquina ocupada. Finalize a sessão primeiro.'
                ], 422);
            }

            // Validar se máquina está reservada
            if ($machine->status === 'reserved') {
                $currentChecklist = $machine->getCurrentChecklist();
                if ($currentChecklist) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Máquina reservada para um checklist. Cancele o checklist primeiro.'
                    ], 422);
                }
            }

            $machine->active = false;
            $machine->save();

            \Log::info("Máquina desativada", [
                'machine_id' => $machine->id,
                'machine_name' => $machine->name,
                'reason' => $reason,
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Máquina desativada com sucesso.',
                'machine' => [
                    'id' => $machine->id,
                    'name' => $machine->name,
                    'status' => $machine->status,
                    'is_active' => $machine->active
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar estado da máquina.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}