<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SafetyChecklist;
use App\Models\Machine;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $unitId = $user->unit_id;

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
        $activeMachines = Machine::where('unit_id', $unitId)
            ->where('is_active', true)
            ->count();

        // Patients count
        $totalPatients = Patient::where('unit_id', $unitId)
            ->where('is_active', true)
            ->count();

        $patientsLastMonth = Patient::where('unit_id', $unitId)
            ->where('is_active', true)
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
            ->where('phase', 'dialise')
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
     * Get sessions by shift (for chart)
     */
    public function sessionsByShift(Request $request)
    {
        $user = $request->user();
        $unitId = $user->unit_id;

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
            ->groupBy('date', 'shift')
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
     * Get recent activity
     */
    public function recentActivity(Request $request)
    {
        $user = $request->user();
        $unitId = $user->unit_id;

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
                'description' => 'Máquina ' . $checklist->machine->code . ' - ' . $checklist->patient->name,
                'time' => $checklist->created_at->diffForHumans(),
                'dotColor' => $checklist->phase === 'dialise' ? 'bg-green-500' : 'bg-yellow-500',
                'timestamp' => $checklist->created_at->timestamp,
            ];
        }

        // Recent patients
        $recentPatients = Patient::where('unit_id', $unitId)
            ->where('is_active', true)
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
