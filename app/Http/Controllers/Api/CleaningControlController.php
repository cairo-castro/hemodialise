<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CleaningControl;
use Illuminate\Http\Request;

class CleaningControlController extends Controller
{
    public function index(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'scoped_unit_id' => 'nullable|integer|exists:units,id',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'machine_id' => 'nullable|integer|exists:machines,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        // Build query with optimized eager loading
        $query = CleaningControl::query()
            ->with([
                'machine:id,name,identifier,unit_id',
                'user:id,name,email'
            ])
            ->select([
                'id',
                'machine_id',
                'unit_id',
                'user_id',
                'cleaning_date',
                'cleaning_time',
                'shift',
                'daily_cleaning',
                'weekly_cleaning',
                'monthly_cleaning',
                'special_cleaning',
                'hd_machine_cleaning',
                'osmosis_cleaning',
                'serum_support_cleaning',
                'chemical_disinfection',
                'external_cleaning_done',
                'internal_cleaning_done',
                'filter_replacement',
                'system_disinfection',
                'cleaning_products_used',
                'cleaning_procedure',
                'observations',
                'created_at',
                'updated_at'
            ]);

        // Apply filters
        if ($scopedUnitId = $validated['scoped_unit_id'] ?? null) {
            $query->where('unit_id', $scopedUnitId);
        }

        if ($machineId = $validated['machine_id'] ?? null) {
            $query->where('machine_id', $machineId);
        }

        if ($dateFrom = $validated['date_from'] ?? null) {
            $query->whereDate('cleaning_date', '>=', $dateFrom);
        }

        if ($dateTo = $validated['date_to'] ?? null) {
            $query->whereDate('cleaning_date', '<=', $dateTo);
        }

        // Order by most recent first
        $query->latest('cleaning_date')->latest('cleaning_time')->latest('id');

        // Paginate with validation
        $perPage = min($validated['per_page'] ?? 50, 100);

        return response()->json($query->paginate($perPage));
    }

    public function show(CleaningControl $cleaningControl)
    {
        // Eager load relationships with specific columns
        $cleaningControl->load([
            'machine:id,name,identifier,unit_id',
            'user:id,name,email'
        ]);

        return response()->json([
            'success' => true,
            'data' => $cleaningControl
        ]);
    }

    public function store(Request $request)
    {
        // Validate request data
        $data = $request->validate([
            'machine_id' => 'required|integer|exists:machines,id',
            'cleaning_date' => 'required|date|before_or_equal:today',
            'cleaning_time' => 'required|date_format:H:i',
            'shift' => 'required|in:matutino,vespertino,noturno,morning,afternoon,night',
            'daily_cleaning' => 'nullable|boolean',
            'weekly_cleaning' => 'nullable|boolean',
            'monthly_cleaning' => 'nullable|boolean',
            'special_cleaning' => 'nullable|boolean',
            'hd_machine_cleaning' => 'nullable|in:C,NC,NA',
            'osmosis_cleaning' => 'nullable|in:C,NC,NA',
            'serum_support_cleaning' => 'nullable|in:C,NC,NA',
            'chemical_disinfection' => 'nullable|boolean',
            'cleaning_products_used' => 'nullable|string|max:500',
            'cleaning_procedure' => 'nullable|string|max:2000',
            'external_cleaning_done' => 'nullable|boolean',
            'internal_cleaning_done' => 'nullable|boolean',
            'filter_replacement' => 'nullable|boolean',
            'system_disinfection' => 'nullable|boolean',
            'observations' => 'nullable|string|max:2000',
        ]);

        // Set authenticated user
        $data['user_id'] = $request->user()->id;

        // Get machine and verify it exists, then set unit_id
        $machine = \App\Models\Machine::findOrFail($data['machine_id']);
        $data['unit_id'] = $machine->unit_id;

        // Ensure at least one cleaning type is selected
        if (
            !($data['daily_cleaning'] ?? false) &&
            !($data['weekly_cleaning'] ?? false) &&
            !($data['monthly_cleaning'] ?? false) &&
            !($data['special_cleaning'] ?? false)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Selecione pelo menos um tipo de limpeza (diária, semanal, mensal ou especial).',
                'errors' => [
                    'cleaning_type' => ['Pelo menos um tipo de limpeza deve ser selecionado.']
                ]
            ], 422);
        }

        // Create the cleaning control record
        $cleaning = CleaningControl::create($data);

        // Load relationships for response
        $cleaning->load([
            'machine:id,name,identifier,unit_id',
            'user:id,name,email'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Limpeza registrada com sucesso.',
            'data' => $cleaning
        ], 201);
    }

    public function update(Request $request, CleaningControl $cleaningControl)
    {
        // Validate update data with proper constraints
        $data = $request->validate([
            'cleaning_date' => 'nullable|date|before_or_equal:today',
            'cleaning_time' => 'nullable|date_format:H:i',
            'shift' => 'nullable|in:matutino,vespertino,noturno,morning,afternoon,night',
            'daily_cleaning' => 'nullable|boolean',
            'weekly_cleaning' => 'nullable|boolean',
            'monthly_cleaning' => 'nullable|boolean',
            'special_cleaning' => 'nullable|boolean',
            'hd_machine_cleaning' => 'nullable|in:C,NC,NA',
            'osmosis_cleaning' => 'nullable|in:C,NC,NA',
            'serum_support_cleaning' => 'nullable|in:C,NC,NA',
            'chemical_disinfection' => 'nullable|boolean',
            'external_cleaning_done' => 'nullable|boolean',
            'internal_cleaning_done' => 'nullable|boolean',
            'filter_replacement' => 'nullable|boolean',
            'system_disinfection' => 'nullable|boolean',
            'cleaning_products_used' => 'nullable|string|max:500',
            'cleaning_procedure' => 'nullable|string|max:2000',
            'observations' => 'nullable|string|max:2000',
        ]);

        // Update the cleaning control record
        $cleaningControl->update($data);

        // Refresh and load relationships
        $cleaningControl->fresh()->load([
            'machine:id,name,identifier,unit_id',
            'user:id,name,email'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Limpeza atualizada com sucesso.',
            'data' => $cleaningControl
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
        // Validate input
        $validated = $request->validate([
            'scoped_unit_id' => 'nullable|integer|exists:units,id',
        ]);

        $scopedUnitId = $validated['scoped_unit_id'] ?? null;
        $today = now()->toDateString();

        // Cache key based on unit and date
        $cacheKey = 'cleaning_stats_' . ($scopedUnitId ?? 'all') . '_' . $today;

        // Cache stats for 5 minutes to reduce database load
        $stats = \Cache::remember($cacheKey, 300, function () use ($scopedUnitId, $today) {
            $query = CleaningControl::query();

            if ($scopedUnitId) {
                $query->where('unit_id', $scopedUnitId);
            }

            // Total today
            $totalToday = (clone $query)->whereDate('cleaning_date', $today)->count();

            // Daily cleanings today
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

            return [
                'total_today' => $totalToday,
                'daily' => $daily,
                'weekly' => $weekly,
                'monthly' => $monthly,
            ];
        });

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
