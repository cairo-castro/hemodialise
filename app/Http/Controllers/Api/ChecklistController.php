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

        // Verificar se já existe um checklist para o mesmo paciente, máquina, data e turno
        $existingChecklist = SafetyChecklist::where([
            'patient_id' => $data['patient_id'],
            'machine_id' => $data['machine_id'],
            'session_date' => now()->toDateString(),
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

        $data['user_id'] = $request->user()->id;
        $data['session_date'] = now()->toDateString();
        $data['current_phase'] = 'pre_dialysis';
        $data['pre_dialysis_started_at'] = now();

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

    public function active(Request $request)
    {
        // Include both active and paused checklists (all in-progress checklists)
        $query = SafetyChecklist::whereNotIn('current_phase', ['completed', 'interrupted'])
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
        // Get recent checklists (last 24 hours) with limit parameter
        $limit = $request->input('limit', 10);

        $query = SafetyChecklist::with(['machine', 'patient', 'user'])
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        // Filtrar por unidade (todos os usuários respeitam a unidade ativa)
        $scopedUnitId = $request->get('scoped_unit_id');
        $query->forUnit($scopedUnitId);  // Using query scope

        $recentChecklists = $query->get();

        return response()->json([
            'success' => true,
            'checklists' => $recentChecklists,
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
}