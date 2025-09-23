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
            $machines = Machine::available()
                ->orderBy('name')
                ->get(['id', 'name', 'identifier', 'description', 'status']);

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
            $machines = Machine::active()
                ->with(['getCurrentChecklist.patient'])
                ->orderBy('name')
                ->get();

            // Adicionar status info para cada máquina
            $machines = $machines->map(function ($machine) {
                $currentChecklist = $machine->getCurrentChecklist();

                return [
                    'id' => $machine->id,
                    'name' => $machine->name,
                    'identifier' => $machine->identifier,
                    'description' => $machine->description,
                    'status' => $machine->status,
                    'is_available' => $machine->isAvailable(),
                    'is_occupied' => $machine->isOccupied(),
                    'is_reserved' => $machine->isReserved(),
                    'current_checklist' => $currentChecklist ? [
                        'id' => $currentChecklist->id,
                        'current_phase' => $currentChecklist->current_phase,
                        'patient_name' => $currentChecklist->patient->name ?? null,
                        'started_at' => $currentChecklist->created_at,
                        'is_paused' => $currentChecklist->isPaused(),
                    ] : null
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
}