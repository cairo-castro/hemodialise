<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CleaningControl;
use Illuminate\Http\Request;

class CleaningControlController extends Controller
{
    public function index(Request $request)
    {
        $query = CleaningControl::with(['machine', 'user']);

        // Filter by unit
        $scopedUnitId = $request->get('scoped_unit_id');
        if ($scopedUnitId) {
            $query->where('unit_id', $scopedUnitId);
        }

        return response()->json($query->latest('cleaning_date')->paginate(50));
    }

    public function show(CleaningControl $cleaningControl)
    {
        return response()->json($cleaningControl->load(['machine', 'user']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'cleaning_date' => 'required|date',
            'cleaning_time' => 'required|date_format:H:i',
            'shift' => 'required|in:matutino,vespertino,noturno',
            'daily_cleaning' => 'nullable|boolean',
            'weekly_cleaning' => 'nullable|boolean',
            'monthly_cleaning' => 'nullable|boolean',
            'special_cleaning' => 'nullable|boolean',
            'hd_machine_cleaning' => 'nullable|boolean',
            'osmosis_cleaning' => 'nullable|boolean',
            'serum_support_cleaning' => 'nullable|boolean',
            'chemical_disinfection' => 'nullable|boolean',
            'cleaning_products_used' => 'nullable|string',
            'cleaning_procedure' => 'nullable|string',
            'external_cleaning_done' => 'nullable|boolean',
            'internal_cleaning_done' => 'nullable|boolean',
            'filter_replacement' => 'nullable|boolean',
            'system_disinfection' => 'nullable|boolean',
            'observations' => 'nullable|string',
        ]);

        $data['user_id'] = $request->user()->id;

        // Get machine to set unit_id
        $machine = \App\Models\Machine::find($data['machine_id']);
        $data['unit_id'] = $machine->unit_id;

        $cleaning = CleaningControl::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Limpeza registrada com sucesso.',
            'cleaning' => $cleaning->load(['machine', 'user'])
        ], 201);
    }

    public function update(Request $request, CleaningControl $cleaningControl)
    {
        $data = $request->validate([
            'hd_machine_cleaning' => 'nullable|boolean',
            'osmosis_cleaning' => 'nullable|boolean',
            'serum_support_cleaning' => 'nullable|boolean',
            'chemical_disinfection' => 'nullable|boolean',
            'external_cleaning_done' => 'nullable|boolean',
            'internal_cleaning_done' => 'nullable|boolean',
            'filter_replacement' => 'nullable|boolean',
            'system_disinfection' => 'nullable|boolean',
            'cleaning_products_used' => 'nullable|string',
            'cleaning_procedure' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        $cleaningControl->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Limpeza atualizada com sucesso.',
            'cleaning' => $cleaningControl->fresh()->load(['machine', 'user'])
        ]);
    }

    public function destroy(CleaningControl $cleaningControl)
    {
        $cleaningControl->delete();

        return response()->json([
            'success' => true,
            'message' => 'Limpeza excluída com sucesso.'
        ]);
    }

    public function stats(Request $request)
    {
        $scopedUnitId = $request->get('scoped_unit_id');
        $today = now()->toDateString();

        $query = CleaningControl::query();

        if ($scopedUnitId) {
            $query->where('unit_id', $scopedUnitId);
        }

        // Total today
        $totalToday = (clone $query)->whereDate('cleaning_date', $today)->count();

        // Daily cleanings
        $daily = (clone $query)
            ->whereDate('cleaning_date', $today)
            ->where('daily_cleaning', true)
            ->count();

        // Weekly cleanings (last 7 days)
        $weekly = (clone $query)
            ->whereDate('cleaning_date', '>=', now()->subDays(7)->toDateString())
            ->where('weekly_cleaning', true)
            ->count();

        // Monthly cleanings (last 30 days)
        $monthly = (clone $query)
            ->whereDate('cleaning_date', '>=', now()->subDays(30)->toDateString())
            ->where('monthly_cleaning', true)
            ->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_today' => $totalToday,
                'daily' => $daily,
                'weekly' => $weekly,
                'monthly' => $monthly,
            ]
        ]);
    }
}
