<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SafetyChecklist;
use App\Models\Machine;
use App\Models\Patient;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SafetyChecklistController extends Controller
{
    public function index()
    {
        $checklists = SafetyChecklist::with(['patient', 'machine', 'user'])
            ->latest()
            ->paginate(10);

        return view('frontend.safety.index', compact('checklists'));
    }

    public function create()
    {
        $machines = Machine::where('active', true)->get();
        $patients = Patient::where('active', true)->get();

        return view('frontend.safety.create', compact('machines', 'patients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'machine_id' => 'required|exists:machines,id',
            'session_date' => 'required|date',
            'shift' => 'required|in:morning,afternoon,night',
            'machine_disinfected' => 'boolean',
            'capillary_lines_identified' => 'boolean',
            'patient_identification_confirmed' => 'boolean',
            'vascular_access_evaluated' => 'boolean',
            'vital_signs_checked' => 'boolean',
            'medications_reviewed' => 'boolean',
            'dialyzer_membrane_checked' => 'boolean',
            'equipment_functioning_verified' => 'boolean',
            'observations' => 'nullable|string',
            'incidents' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        SafetyChecklist::create($validated);

        return redirect()->route('frontend.safety.index')
            ->with('success', 'Checklist de segurança registrado com sucesso!');
    }

    public function show(SafetyChecklist $checklist)
    {
        $checklist->load(['patient', 'machine', 'user']);
        return view('frontend.safety.show', compact('checklist'));
    }

    public function edit(SafetyChecklist $checklist)
    {
        $machines = Machine::where('active', true)->get();
        $patients = Patient::where('active', true)->get();

        return view('frontend.safety.edit', compact('checklist', 'machines', 'patients'));
    }

    public function update(Request $request, SafetyChecklist $checklist)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'machine_id' => 'required|exists:machines,id',
            'session_date' => 'required|date',
            'shift' => 'required|in:morning,afternoon,night',
            'machine_disinfected' => 'boolean',
            'capillary_lines_identified' => 'boolean',
            'patient_identification_confirmed' => 'boolean',
            'vascular_access_evaluated' => 'boolean',
            'vital_signs_checked' => 'boolean',
            'medications_reviewed' => 'boolean',
            'dialyzer_membrane_checked' => 'boolean',
            'equipment_functioning_verified' => 'boolean',
            'observations' => 'nullable|string',
            'incidents' => 'nullable|string',
        ]);

        $checklist->update($validated);

        return redirect()->route('frontend.safety.index')
            ->with('success', 'Checklist atualizado com sucesso!');
    }
}
