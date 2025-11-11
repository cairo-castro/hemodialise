<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SafetyChecklist;
use App\Models\Machine;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics with caching
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        // Get active unit from user (handles global access users)
        $activeUnit = $user->getActiveUnit();
        $unitId = $activeUnit ? $activeUnit->id : null;

        if (!$unitId) {
            return response()->json([
                'success' => true,
                'data' => [
                    'checklists' => ['value' => 0, 'change' => 0],
                    'machines' => ['active' => 0, 'total' => 0, 'change' => 0],
                    'patients' => ['value' => 0, 'change' => 0],
                    'conformity' => ['value' => 0, 'change' => 0],
                ]
            ]);
        }

        // Cache key specific to unit and current minute
        $cacheKey = "dashboard_stats_unit_{$unitId}_" . now()->format('Y-m-d_H:i');

        return Cache::remember($cacheKey, 60, function () use ($unitId) {
            return $this->calculateStats($unitId);
        });
    }

    /**
     * Calculate dashboard statistics
     */
    private function calculateStats($unitId)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Checklists today vs yesterday
        $checklistsToday = SafetyChecklist::where('unit_id', $unitId)
            ->whereDate('created_at', $today)
            ->count();

        $checklistsYesterday = SafetyChecklist::where('unit_id', $unitId)
            ->whereDate('created_at', $yesterday)
            ->count();

        $checklistsChange = $checklistsYesterday > 0
            ? round((($checklistsToday - $checklistsYesterday) / $checklistsYesterday) * 100, 1)
            : 0;

        // Machines stats
        $totalMachines = Machine::where('unit_id', $unitId)->count();
        $activeMachines = Machine::where('unit_id', $unitId)->count(); // All machines are considered active if no is_active column

        // Patients count
        $totalPatients = Patient::where('unit_id', $unitId)->count();

        $patientsLastMonth = Patient::where('unit_id', $unitId)
            ->whereDate('created_at', '<=', Carbon::now()->subMonth())
            ->count();

        $patientsChange = $patientsLastMonth > 0
            ? round((($totalPatients - $patientsLastMonth) / $patientsLastMonth) * 100, 1)
            : 0;

        // Conformidade (checklists completados vs total)
        $totalChecklistsMonth = SafetyChecklist::where('unit_id', $unitId)
            ->whereMonth('created_at', now()->month)
            ->count();

        $completedChecklistsMonth = SafetyChecklist::where('unit_id', $unitId)
            ->whereMonth('created_at', now()->month)
            ->where('current_phase', 'completed')
            ->count();

        $conformityRate = $totalChecklistsMonth > 0
            ? round(($completedChecklistsMonth / $totalChecklistsMonth) * 100, 0)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'checklists' => [
                    'value' => $checklistsToday,
                    'change' => $checklistsChange,
                ],
                'machines' => [
                    'active' => $activeMachines,
                    'total' => $totalMachines,
                    'change' => 0, // Could calculate based on last week
                ],
                'patients' => [
                    'value' => $totalPatients,
                    'change' => $patientsChange,
                ],
                'conformity' => [
                    'value' => $conformityRate,
                    'change' => 5, // Could calculate based on last month
                ],
            ]
        ]);
    }

    /**
     * Get sessions by shift (for chart) with caching
     */
    public function sessionsByShift(Request $request)
    {
        $user = $request->user();

        // Get active unit from user (handles global access users)
        $activeUnit = $user->getActiveUnit();
        $unitId = $activeUnit ? $activeUnit->id : null;

        if (!$unitId) {
            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => [],
                    'datasets' => [
                        ['label' => 'Matutino', 'data' => [], 'backgroundColor' => 'rgba(59, 130, 246, 0.5)'],
                        ['label' => 'Vespertino', 'data' => [], 'backgroundColor' => 'rgba(16, 185, 129, 0.5)'],
                        ['label' => 'Noturno', 'data' => [], 'backgroundColor' => 'rgba(139, 92, 246, 0.5)'],
                    ],
                ]
            ]);
        }

        // Cache for 5 minutes (chart data changes less frequently)
        $cacheKey = "dashboard_sessions_unit_{$unitId}_" . now()->format('Y-m-d_H:i');

        return Cache::remember($cacheKey, 300, function () use ($unitId) {
            return $this->calculateSessionsByShift($unitId);
        });
    }

    /**
     * Calculate sessions by shift
     */
    private function calculateSessionsByShift($unitId)
    {
        // Get last 7 days of data
        $startDate = Carbon::now()->subDays(6)->startOfDay();

        $sessions = SafetyChecklist::where('unit_id', $unitId)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('CASE
                    WHEN HOUR(created_at) BETWEEN 6 AND 11 THEN "Matutino"
                    WHEN HOUR(created_at) BETWEEN 12 AND 17 THEN "Vespertino"
                    ELSE "Noturno"
                END as shift'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(
                DB::raw('DATE(created_at)'),
                DB::raw('CASE
                    WHEN HOUR(created_at) BETWEEN 6 AND 11 THEN "Matutino"
                    WHEN HOUR(created_at) BETWEEN 12 AND 17 THEN "Vespertino"
                    ELSE "Noturno"
                END')
            )
            ->orderBy('date')
            ->get();

        // Format for chart
        $chartData = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Matutino',
                    'data' => [],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                ],
                [
                    'label' => 'Vespertino',
                    'data' => [],
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                ],
                [
                    'label' => 'Noturno',
                    'data' => [],
                    'backgroundColor' => 'rgba(139, 92, 246, 0.5)',
                ],
            ],
        ];

        // Fill data for last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartData['labels'][] = $date->format('d/m');

            $dateStr = $date->format('Y-m-d');
            $matutino = $sessions->where('date', $dateStr)->where('shift', 'Matutino')->first();
            $vespertino = $sessions->where('date', $dateStr)->where('shift', 'Vespertino')->first();
            $noturno = $sessions->where('date', $dateStr)->where('shift', 'Noturno')->first();

            $chartData['datasets'][0]['data'][] = $matutino ? $matutino->count : 0;
            $chartData['datasets'][1]['data'][] = $vespertino ? $vespertino->count : 0;
            $chartData['datasets'][2]['data'][] = $noturno ? $noturno->count : 0;
        }

        return response()->json([
            'success' => true,
            'data' => $chartData,
        ]);
    }

    /**
     * Get recent activity with caching
     */
    public function recentActivity(Request $request)
    {
        $user = $request->user();

        // Get active unit from user (handles global access users)
        $activeUnit = $user->getActiveUnit();
        $unitId = $activeUnit ? $activeUnit->id : null;

        if (!$unitId) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        // Cache for 1 minute (activity should update frequently)
        $cacheKey = "dashboard_activity_unit_{$unitId}_" . now()->format('Y-m-d_H:i');

        return Cache::remember($cacheKey, 60, function () use ($unitId) {
            return $this->calculateRecentActivity($unitId);
        });
    }

    /**
     * Calculate recent activity
     */
    private function calculateRecentActivity($unitId)
    {
        $activities = [];

        // Recent checklists
        $recentChecklists = SafetyChecklist::where('unit_id', $unitId)
            ->with(['machine', 'patient', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentChecklists as $checklist) {
            $activities[] = [
                'type' => 'checklist',
                'title' => 'Checklist Concluído',
                'description' => 'Máquina ' . $checklist->machine->identifier . ' - ' . $checklist->patient->full_name,
                'time' => $checklist->created_at->diffForHumans(),
                'dotColor' => $checklist->current_phase === 'completed' ? 'bg-green-500' : 'bg-yellow-500',
                'timestamp' => $checklist->created_at->timestamp,
            ];
        }

        // Recent patients
        $recentPatients = Patient::where('unit_id', $unitId)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentPatients as $patient) {
            $activities[] = [
                'type' => 'patient',
                'title' => 'Novo Paciente',
                'description' => $patient->name . ' - ' . $patient->medical_record,
                'time' => $patient->created_at->diffForHumans(),
                'dotColor' => 'bg-blue-500',
                'timestamp' => $patient->created_at->timestamp,
            ];
        }

        // Sort by timestamp
        usort($activities, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        // Remove timestamp and return top 6
        $activities = array_slice($activities, 0, 6);
        foreach ($activities as &$activity) {
            unset($activity['timestamp']);
        }

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }
}
