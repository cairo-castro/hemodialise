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
            // Get all machines (not just active ones)
            $machines = Machine::orderBy('name')->get();

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
}