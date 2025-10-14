<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SafetyChecklist;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function index(Request $request)
    {
        $query = SafetyChecklist::with(['machine', 'patient']);

        // Técnicos só veem máquinas da sua unidade
        if ($request->user()->isTecnico()) {
            $query->whereHas('machine', function($q) use ($request) {
                $q->where('unit_id', $request->user()->unit_id);
            });
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'patient_id' => 'required|exists:patients,id',
            'shift' => 'required|in:matutino,vespertino,noturno,madrugada',
            'observations' => 'nullable|string',
        ]);

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

        // Técnicos só veem checklists da sua unidade
        if ($request->user()->isTecnico()) {
            $query->whereHas('machine', function($q) use ($request) {
                $q->where('unit_id', $request->user()->unit_id);
            });
        }

        $activeChecklists = $query->get();

        return response()->json([
            'success' => true,
            'checklists' => $activeChecklists,
            'total' => $activeChecklists->count(),
        ]);
    }

    public function pause(SafetyChecklist $checklist)
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

        $checklist->pauseSession();

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