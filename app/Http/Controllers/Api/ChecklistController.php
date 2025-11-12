<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChecklistRequest;
use App\Models\SafetyChecklist;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function index(Request $request)
    {
        $query = SafetyChecklist::with(['machine', 'patient']);

        // Filtrar por unidade (todos os usuários respeitam a unidade ativa)
        $scopedUnitId = $request->get('scoped_unit_id');
        $query->forUnit($scopedUnitId);  // Using query scope

        return response()->json($query->latest()->paginate(20));
    }

    public function store(StoreChecklistRequest $request)
    {
        $data = $request->validated();

        // Use provided session_date or default to today
        $sessionDate = isset($data['session_date']) ? $data['session_date'] : now()->toDateString();
        $sessionTime = isset($data['session_time']) ? $data['session_time'] : now()->format('H:i');

        // Verificar se já existe um checklist para o mesmo paciente, máquina, data e turno
        $existingChecklist = SafetyChecklist::where([
            'patient_id' => $data['patient_id'],
            'machine_id' => $data['machine_id'],
            'session_date' => $sessionDate,
            'shift' => $data['shift']
        ])->first();

        if ($existingChecklist) {
            // Se já existe, retornar o checklist existente
            return response()->json([
                'success' => true,
                'message' => 'Checklist já existe para esta sessão. Continuando checklist existente.',
                'checklist' => $existingChecklist->load(['machine', 'patient']),
                'current_time' => now()->format('H:i:s'),
                'current_date' => now()->format('d/m/Y'),
                'resumed' => true
            ], 200);
        }

        // Combine date and time for pre_dialysis_started_at
        $startedAt = \Carbon\Carbon::parse($sessionDate . ' ' . $sessionTime);

        $data['user_id'] = $request->user()->id;
        $data['session_date'] = $sessionDate;
        $data['current_phase'] = 'pre_dialysis';
        $data['pre_dialysis_started_at'] = $startedAt;

        // Preenche unit_id explicitamente da máquina (performance: zero overhead)
        $machine = \App\Models\Machine::find($data['machine_id']);
        $data['unit_id'] = $machine->unit_id;

        $checklist = SafetyChecklist::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Novo checklist criado com sucesso.',
            'checklist' => $checklist->load(['machine', 'patient']),
            'current_time' => now()->format('H:i:s'),
            'current_date' => now()->format('d/m/Y'),
            'resumed' => false
        ], 201);
    }

    public function updatePhase(Request $request, SafetyChecklist $checklist)
    {
        $data = $request->validate([
            'phase_data' => 'required|array',
            'observations' => 'nullable|string',
            'item_observations' => 'nullable|array',
        ]);

        // Update checklist items
        foreach ($data['phase_data'] as $key => $value) {
            if (in_array($key, $checklist->getFillable())) {
                $checklist->{$key} = $value;
            }
        }

        if (isset($data['observations'])) {
            $checklist->observations = $data['observations'];
        }

        // Atualizar observações de itens individuais
        if (isset($data['item_observations'])) {
            // Merge com observações existentes
            $existingObservations = $checklist->item_observations ?? [];
            $checklist->item_observations = array_merge(
                $existingObservations,
                $data['item_observations']
            );
        }

        $checklist->save();

        // Check if phase can be advanced
        $canAdvance = $checklist->canAdvanceToNextPhase();

        return response()->json([
            'success' => true,
            'checklist' => $checklist->fresh()->load(['machine', 'patient']),
            'can_advance' => $canAdvance,
            'phase_completion' => $checklist->getPhaseCompletionPercentage($checklist->current_phase),
            'current_time' => now()->format('H:i:s'),
        ]);
    }

    public function advancePhase(SafetyChecklist $checklist)
    {
        if (!$checklist->canAdvanceToNextPhase()) {
            return response()->json([
                'success' => false,
                'message' => 'Complete todos os itens da fase atual para continuar.'
            ], 422);
        }

        $oldPhase = $checklist->current_phase;
        $checklist->advanceToNextPhase();

        return response()->json([
            'success' => true,
            'message' => 'Fase avançada com sucesso!',
            'previous_phase' => $oldPhase,
            'current_phase' => $checklist->current_phase,
            'checklist' => $checklist->fresh()->load(['machine', 'patient']),
            'current_time' => now()->format('H:i:s'),
        ]);
    }

    public function interrupt(Request $request, SafetyChecklist $checklist)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $checklist->interruptSession($data['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Checklist interrompido com sucesso.',
            'checklist' => $checklist->fresh()->load(['machine', 'patient']),
            'interrupted_at' => $checklist->interrupted_at->format('d/m/Y H:i:s'),
        ]);
    }

    public function show(SafetyChecklist $checklist)
    {
        return response()->json($checklist->load(['machine', 'patient', 'user']));
    }

    public function update(Request $request, SafetyChecklist $checklist)
    {
        // Only allow updates if checklist is not completed
        if ($checklist->current_phase === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível editar um checklist concluído.'
            ], 422);
        }

        $data = $request->validate([
            'observations' => 'nullable|string',
            // Pre-dialysis items
            'machine_disinfected' => 'nullable|boolean',
            'capillary_lines_identified' => 'nullable|boolean',
            'reagent_test_performed' => 'nullable|boolean',
            'pressure_sensors_verified' => 'nullable|boolean',
            'air_bubble_detector_verified' => 'nullable|boolean',
            'patient_identification_confirmed' => 'nullable|boolean',
            'vascular_access_evaluated' => 'nullable|boolean',
            'av_fistula_arm_washed' => 'nullable|boolean',
            'patient_weighed' => 'nullable|boolean',
            'vital_signs_checked' => 'nullable|boolean',
            'medications_reviewed' => 'nullable|boolean',
            'dialyzer_membrane_checked' => 'nullable|boolean',
            'equipment_functioning_verified' => 'nullable|boolean',
            // During session items
            'dialysis_parameters_verified' => 'nullable|boolean',
            'heparin_double_checked' => 'nullable|boolean',
            'antisepsis_performed' => 'nullable|boolean',
            'vascular_access_monitored' => 'nullable|boolean',
            'vital_signs_monitored_during' => 'nullable|boolean',
            'patient_comfort_assessed' => 'nullable|boolean',
            'fluid_balance_monitored' => 'nullable|boolean',
            'alarms_responded' => 'nullable|boolean',
            // Post-dialysis items
            'session_completed_safely' => 'nullable|boolean',
            'vascular_access_secured' => 'nullable|boolean',
            'patient_vital_signs_stable' => 'nullable|boolean',
            'complications_assessed' => 'nullable|boolean',
            'equipment_cleaned' => 'nullable|boolean',
        ]);

        $checklist->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Checklist atualizado com sucesso.',
            'checklist' => $checklist->fresh()->load(['machine', 'patient', 'user'])
        ]);
    }

    public function destroy(SafetyChecklist $checklist)
    {
        // Only allow soft delete if checklist is not completed
        if ($checklist->current_phase === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir um checklist concluído.'
            ], 422);
        }

        $checklist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Checklist excluído com sucesso.'
        ]);
    }

    public function active(Request $request)
    {
        // Include both active and paused checklists (all in-progress checklists)
        // Also include checklists with NULL current_phase (incomplete checklists)
        $query = SafetyChecklist::where(function ($q) {
                $q->whereNotIn('current_phase', ['completed', 'interrupted'])
                  ->orWhereNull('current_phase');
            })
            ->where('is_interrupted', false)
            ->with(['machine', 'patient', 'user'])
            ->orderBy('created_at', 'desc');

        // Filtrar por unidade (todos os usuários respeitam a unidade ativa)
        $scopedUnitId = $request->get('scoped_unit_id');
        $query->forUnit($scopedUnitId);  // Using query scope

        $activeChecklists = $query->get();

        return response()->json([
            'success' => true,
            'checklists' => $activeChecklists,
            'total' => $activeChecklists->count(),
        ]);
    }

    public function recent(Request $request)
    {
        // Get recent checklists with limit parameter (no time restriction)
        // Prioritize in-progress checklists (including NULL current_phase)
        $limit = $request->input('limit', 10);

        // Filtrar por unidade (todos os usuários respeitam a unidade ativa)
        $scopedUnitId = $request->get('scoped_unit_id');

        // Get in-progress checklists first
        $inProgressQuery = SafetyChecklist::with(['machine', 'patient', 'user'])
            ->where(function ($q) {
                $q->whereNotIn('current_phase', ['completed', 'interrupted'])
                  ->orWhereNull('current_phase');
            })
            ->where('is_interrupted', false)
            ->orderBy('created_at', 'desc');

        $inProgressQuery->forUnit($scopedUnitId);
        $inProgressChecklists = $inProgressQuery->get();

        // If we don't have enough, get completed/interrupted ones
        $remaining = $limit - $inProgressChecklists->count();

        if ($remaining > 0) {
            $completedQuery = SafetyChecklist::with(['machine', 'patient', 'user'])
                ->whereIn('current_phase', ['completed', 'interrupted'])
                ->orderBy('created_at', 'desc')
                ->limit($remaining);

            $completedQuery->forUnit($scopedUnitId);
            $completedChecklists = $completedQuery->get();

            $recentChecklists = $inProgressChecklists->concat($completedChecklists);
        } else {
            $recentChecklists = $inProgressChecklists->take($limit);
        }

        return response()->json([
            'success' => true,
            'data' => $recentChecklists,
            'total' => $recentChecklists->count(),
        ]);
    }

    public function pause(Request $request, SafetyChecklist $checklist)
    {
        if ($checklist->isPaused()) {
            return response()->json([
                'success' => false,
                'message' => 'Checklist já está pausado.'
            ], 422);
        }

        if (!$checklist->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Checklist não pode ser pausado no estado atual.'
            ], 422);
        }

        // Accept reason from request, default to 'manual'
        $reason = $request->input('reason', 'manual');
        $checklist->pauseSession($reason);

        return response()->json([
            'success' => true,
            'message' => 'Checklist pausado com sucesso.',
            'checklist' => $checklist->fresh()->load(['machine', 'patient']),
        ]);
    }

    public function resume(SafetyChecklist $checklist)
    {
        if (!$checklist->isPaused()) {
            return response()->json([
                'success' => false,
                'message' => 'Checklist não está pausado.'
            ], 422);
        }

        $checklist->resumeSession();

        return response()->json([
            'success' => true,
            'message' => 'Checklist retomado com sucesso.',
            'checklist' => $checklist->fresh()->load(['machine', 'patient']),
        ]);
    }

    public function stats(Request $request)
    {
        $scopedUnitId = $request->get('scoped_unit_id');
        $today = now()->toDateString();

        $query = SafetyChecklist::query();

        if ($scopedUnitId) {
            $query->where('unit_id', $scopedUnitId);
        }

        // Total today (created today)
        $totalToday = (clone $query)->whereDate('created_at', $today)->count();

        // In progress (any checklist not completed or interrupted, regardless of creation date)
        $inProgress = (clone $query)
            ->whereNotIn('current_phase', ['completed', 'interrupted'])
            ->where('is_interrupted', false)
            ->count();

        // Completed today (completed today or created today and already completed)
        $completed = (clone $query)
            ->where('current_phase', 'completed')
            ->whereDate('created_at', $today)
            ->count();

        // Interrupted (any interrupted checklist, regardless of date)
        $interrupted = (clone $query)
            ->where('is_interrupted', true)
            ->whereDate('created_at', '>=', now()->subDays(7)->toDateString())
            ->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_today' => $totalToday,
                'in_progress' => $inProgress,
                'completed' => $completed,
                'interrupted' => $interrupted,
            ]
        ]);
    }
}