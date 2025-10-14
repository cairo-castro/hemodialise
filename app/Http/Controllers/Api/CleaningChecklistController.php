<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCleaningChecklistRequest;
use App\Models\CleaningChecklist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CleaningChecklistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CleaningChecklist::with(['machine', 'user'])
            ->orderBy('checklist_date', 'desc')
            ->orderBy('shift', 'desc');

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('checklist_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('checklist_date', '<=', $request->end_date);
        }

        // Filter by machine
        if ($request->has('machine_id')) {
            $query->where('machine_id', $request->machine_id);
        }

        // Filter by shift
        if ($request->has('shift')) {
            $query->where('shift', $request->shift);
        }

        $checklists = $query->get();

        return response()->json([
            'success' => true,
            'checklists' => $checklists
        ]);
    }

    public function store(StoreCleaningChecklistRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        // Check if checklist already exists
        $existing = CleaningChecklist::where('machine_id', $data['machine_id'])
            ->where('checklist_date', $data['checklist_date'])
            ->where('shift', $data['shift'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Já existe um checklist para esta máquina, data e turno'
            ], 409);
        }

        $checklist = CleaningChecklist::create($data);
        $checklist->load(['machine', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Checklist criado com sucesso',
            'checklist' => $checklist
        ], 201);
    }

    public function show(CleaningChecklist $cleaningChecklist): JsonResponse
    {
        $cleaningChecklist->load(['machine', 'user']);

        return response()->json([
            'success' => true,
            'checklist' => $cleaningChecklist
        ]);
    }

    public function update(Request $request, CleaningChecklist $cleaningChecklist): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'chemical_disinfection_time' => 'nullable|date_format:H:i',
            'chemical_disinfection_completed' => 'boolean',
            'hd_machine_cleaning' => 'nullable|in:C,NC,NA',
            'osmosis_cleaning' => 'nullable|in:C,NC,NA',
            'serum_support_cleaning' => 'nullable|in:C,NC,NA',
            'observations' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $cleaningChecklist->update($validator->validated());
        $cleaningChecklist->load(['machine', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Checklist atualizado com sucesso',
            'checklist' => $cleaningChecklist
        ]);
    }

    public function destroy(CleaningChecklist $cleaningChecklist): JsonResponse
    {
        $cleaningChecklist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Checklist excluído com sucesso'
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $today = now()->toDateString();

        $stats = [
            'total_today' => CleaningChecklist::whereDate('checklist_date', $today)->count(),
            'total_this_month' => CleaningChecklist::whereMonth('checklist_date', now()->month)
                ->whereYear('checklist_date', now()->year)
                ->count(),
            'chemical_disinfection_today' => CleaningChecklist::whereDate('checklist_date', $today)
                ->where('chemical_disinfection_completed', true)
                ->count(),
            'surface_cleaning_today' => CleaningChecklist::whereDate('checklist_date', $today)
                ->whereNotNull('hd_machine_cleaning')
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
