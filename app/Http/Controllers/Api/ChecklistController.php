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
            'checklist_data' => 'required|array',
            'shift' => 'required|string',
            'observations' => 'nullable|string',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['session_date'] = now()->toDateString();

        $checklist = SafetyChecklist::create($data);

        return response()->json($checklist->load(['machine', 'patient']), 201);
    }

    public function show(SafetyChecklist $checklist)
    {
        return response()->json($checklist->load(['machine', 'patient', 'user']));
    }
}