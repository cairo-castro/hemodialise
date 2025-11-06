<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SafetyChecklist;
use App\Models\CleaningControl;
use App\Models\ChemicalDisinfection;
use App\Models\Patient;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Get comprehensive checklists report data
     */
    public function checklists(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $scopedUnitId = $request->get('scoped_unit_id');

        // Basic stats
        $query = SafetyChecklist::whereBetween('created_at', [$startDate, $endDate]);

        if ($scopedUnitId) {
            $query->where('unit_id', $scopedUnitId);
        }

        $total = $query->count();
        $completed = (clone $query)->where('current_phase', 'completed')->count();
        $interrupted = (clone $query)->where('current_phase', 'interrupted')->count();
        $inProgress = $total - $completed - $interrupted;

        $conformityRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        // Stats by shift
        $shiftStats = DB::table('safety_checklists')
            ->select(
                DB::raw('CASE
                    WHEN HOUR(created_at) BETWEEN 6 AND 11 THEN "Matutino"
                    WHEN HOUR(created_at) BETWEEN 12 AND 17 THEN "Vespertino"
                    ELSE "Noturno"
                END as shift'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN current_phase = "completed" THEN 1 ELSE 0 END) as conforming')
            )
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($scopedUnitId) {
            $shiftStats->where('unit_id', $scopedUnitId);
        }

        $shiftStats = $shiftStats
            ->groupBy(DB::raw('CASE
                WHEN HOUR(created_at) BETWEEN 6 AND 11 THEN "Matutino"
                WHEN HOUR(created_at) BETWEEN 12 AND 17 THEN "Vespertino"
                ELSE "Noturno"
            END'))
            ->get()->map(function ($item) {
                $rate = $item->total > 0 ? round(($item->conforming / $item->total) * 100, 1) : 0;
                return [
                    'name' => $item->shift,
                    'total' => $item->total,
                    'conforming' => $item->conforming,
                    'rate' => $rate
                ];
            });

        // Conformity Trend (weekly data within the period)
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $weeks = [];
        $weeklyData = [];

        // Calculate number of weeks in period
        $diffInDays = $start->diffInDays($end);
        $numWeeks = min(ceil($diffInDays / 7), 12); // Max 12 weeks

        for ($i = 0; $i < $numWeeks; $i++) {
            $weekStart = (clone $start)->addDays($i * 7);
            $weekEnd = (clone $weekStart)->addDays(6)->min($end);

            $weekQuery = SafetyChecklist::whereBetween('created_at', [$weekStart, $weekEnd]);

            if ($scopedUnitId) {
                $weekQuery->where('unit_id', $scopedUnitId);
            }

            $weekTotal = $weekQuery->count();
            $weekCompleted = (clone $weekQuery)->where('current_phase', 'completed')->count();
            $weekRate = $weekTotal > 0 ? round(($weekCompleted / $weekTotal) * 100, 1) : 0;

            $weeks[] = 'Sem ' . ($i + 1);
            $weeklyData[] = $weekRate;
        }

        // Phase Distribution
        $phases = [
            'pre_dialysis' => 0,
            'during_dialysis' => 0,
            'post_dialysis' => 0,
            'interrupted' => 0
        ];

        $phaseQuery = SafetyChecklist::whereBetween('created_at', [$startDate, $endDate]);

        if ($scopedUnitId) {
            $phaseQuery->where('unit_id', $scopedUnitId);
        }

        $phaseCounts = $phaseQuery
            ->select('current_phase', DB::raw('COUNT(*) as count'))
            ->groupBy('current_phase')
            ->get();

        foreach ($phaseCounts as $phase) {
            if ($phase->current_phase === 'completed' || $phase->current_phase === 'post_dialysis') {
                $phases['post_dialysis'] += $phase->count;
            } elseif ($phase->current_phase === 'interrupted') {
                $phases['interrupted'] = $phase->count;
            } elseif ($phase->current_phase === 'during_dialysis') {
                $phases['during_dialysis'] = $phase->count;
            } else {
                $phases['pre_dialysis'] += $phase->count;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total' => $total,
                    'conformityRate' => $conformityRate,
                    'completed' => $completed,
                    'interrupted' => $interrupted,
                    'inProgress' => $inProgress
                ],
                'shiftStats' => $shiftStats,
                'conformityTrend' => [
                    'categories' => $weeks,
                    'data' => $weeklyData
                ],
                'phaseDistribution' => [
                    'labels' => ['Pré-Diálise', 'Durante Sessão', 'Pós-Diálise', 'Interrompido'],
                    'series' => [
                        $phases['pre_dialysis'],
                        $phases['during_dialysis'],
                        $phases['post_dialysis'],
                        $phases['interrupted']
                    ]
                ],
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]
        ]);
    }

    /**
     * Get cleaning report data
     */
    public function cleaning(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $scopedUnitId = $request->get('scoped_unit_id');

        // Base query for CleaningControl
        $cleaningQuery = CleaningControl::whereBetween('cleaning_date', [$startDate, $endDate]);

        if ($scopedUnitId) {
            $cleaningQuery->where('unit_id', $scopedUnitId);
        }

        // Basic stats
        $total = $cleaningQuery->count();
        $daily = (clone $cleaningQuery)->where('daily_cleaning', true)->count();
        $weekly = (clone $cleaningQuery)->where('weekly_cleaning', true)->count();
        $monthly = (clone $cleaningQuery)->where('monthly_cleaning', true)->count();
        $special = (clone $cleaningQuery)->where('special_cleaning', true)->count();

        // Chemical disinfections count
        $chemicalQuery = ChemicalDisinfection::whereBetween('disinfection_date', [$startDate, $endDate]);

        if ($scopedUnitId) {
            $chemicalQuery->where('unit_id', $scopedUnitId);
        }

        $chemical = $chemicalQuery->count();

        // Calculate conformity rate (cleanings with all main items completed)
        $conformingCleanings = (clone $cleaningQuery)
            ->where('hd_machine_cleaning', true)
            ->where('osmosis_cleaning', true)
            ->where('serum_support_cleaning', true)
            ->count();

        $conformityRate = $total > 0 ? round(($conformingCleanings / $total) * 100, 1) : 0;

        // Cleaning by type for chart
        $cleaningTypeData = [
            'categories' => ['Diária', 'Semanal', 'Mensal', 'Especial'],
            'data' => [$daily, $weekly, $monthly, $special]
        ];

        // Item conformity (percentage of cleanings where each item was completed)
        $hdMachineRate = $total > 0 ? round(((clone $cleaningQuery)->where('hd_machine_cleaning', true)->count() / $total) * 100, 1) : 0;
        $osmosisRate = $total > 0 ? round(((clone $cleaningQuery)->where('osmosis_cleaning', true)->count() / $total) * 100, 1) : 0;
        $serumSupportRate = $total > 0 ? round(((clone $cleaningQuery)->where('serum_support_cleaning', true)->count() / $total) * 100, 1) : 0;
        $disinfectionRate = $total > 0 ? round(((clone $cleaningQuery)->where('chemical_disinfection', true)->count() / $total) * 100, 1) : 0;

        $itemConformity = [
            'categories' => ['Máquina HD', 'Osmose', 'Suporte Soro', 'Desinfecção'],
            'data' => [$hdMachineRate, $osmosisRate, $serumSupportRate, $disinfectionRate]
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total' => $total,
                    'conformityRate' => $conformityRate,
                    'daily' => $daily,
                    'chemical' => $chemical
                ],
                'cleaningByType' => $cleaningTypeData,
                'itemConformity' => $itemConformity,
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]
        ]);
    }

    /**
     * Get patients report data
     */
    public function patients(Request $request)
    {
        $scopedUnitId = $request->get('scoped_unit_id');

        $query = Patient::query();

        if ($scopedUnitId) {
            $query->where('unit_id', $scopedUnitId);
        }

        $total = $query->count();
        $active = (clone $query)->where('status', 'ativo')->count();
        $inactive = (clone $query)->where('status', 'inativo')->count();
        $transferred = (clone $query)->where('status', 'transferido')->count();
        $discharged = (clone $query)->where('status', 'alta')->count();
        $deceased = (clone $query)->where('status', 'obito')->count();

        // Calculate average age
        $averageAge = DB::table('patients')
            ->selectRaw('AVG(TIMESTAMPDIFF(YEAR, birth_date, CURDATE())) as avg_age')
            ->when($scopedUnitId, function($q) use ($scopedUnitId) {
                return $q->where('unit_id', $scopedUnitId);
            })
            ->whereNotNull('birth_date')
            ->first()
            ->avg_age;

        $averageAge = $averageAge ? round($averageAge, 1) : 0;

        // Age distribution (adjusted ranges to match frontend)
        $ageGroups = DB::table('patients')
            ->select(
                DB::raw('CASE
                    WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 18 AND 30 THEN "18-30"
                    WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 31 AND 45 THEN "31-45"
                    WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 46 AND 60 THEN "46-60"
                    WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 61 AND 75 THEN "61-75"
                    WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) > 75 THEN "76+"
                    ELSE "Outro"
                END as age_group'),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('birth_date');

        if ($scopedUnitId) {
            $ageGroups->where('unit_id', $scopedUnitId);
        }

        $ageGroupData = $ageGroups
            ->groupBy(DB::raw('CASE
                WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 18 AND 30 THEN "18-30"
                WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 31 AND 45 THEN "31-45"
                WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 46 AND 60 THEN "46-60"
                WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 61 AND 75 THEN "61-75"
                WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) > 75 THEN "76+"
                ELSE "Outro"
            END'))
            ->get();

        // Format age data for chart
        $ageCategories = ['18-30', '31-45', '46-60', '61-75', '76+'];
        $ageData = [];
        foreach ($ageCategories as $category) {
            $group = $ageGroupData->firstWhere('age_group', $category);
            $ageData[] = $group ? $group->count : 0;
        }

        // Status distribution
        $statusLabels = ['Ativo', 'Inativo', 'Alta', 'Óbito'];
        $statusData = [$active, $inactive, $discharged, $deceased];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total' => $total,
                    'active' => $active,
                    'averageAge' => $averageAge
                ],
                'statusDistribution' => [
                    'labels' => $statusLabels,
                    'series' => $statusData
                ],
                'ageDistribution' => [
                    'categories' => $ageCategories,
                    'data' => $ageData
                ]
            ]
        ]);
    }

    /**
     * Get unit performance report data
     */
    public function performance(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $scopedUnitId = $request->get('scoped_unit_id');

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Calculate number of weeks
        $diffInDays = $start->diffInDays($end);
        $numWeeks = min(ceil($diffInDays / 7), 12);

        $weeklyPerformance = [
            'categories' => [],
            'checklists' => [],
            'cleanings' => [],
            'conformityRate' => []
        ];

        for ($i = 0; $i < $numWeeks; $i++) {
            $weekStart = (clone $start)->addDays($i * 7);
            $weekEnd = (clone $weekStart)->addDays(6)->min($end);

            $weeklyPerformance['categories'][] = 'Sem ' . ($i + 1);

            // Checklists count
            $checklistQuery = SafetyChecklist::whereBetween('created_at', [$weekStart, $weekEnd]);
            if ($scopedUnitId) {
                $checklistQuery->where('unit_id', $scopedUnitId);
            }
            $checklistCount = $checklistQuery->count();
            $weeklyPerformance['checklists'][] = $checklistCount;

            // Cleanings count
            $cleaningQuery = CleaningControl::whereBetween('cleaning_date', [$weekStart, $weekEnd]);
            if ($scopedUnitId) {
                $cleaningQuery->where('unit_id', $scopedUnitId);
            }
            $cleaningCount = $cleaningQuery->count();
            $weeklyPerformance['cleanings'][] = $cleaningCount;

            // Conformity rate
            $completed = (clone $checklistQuery)->where('current_phase', 'completed')->count();
            $rate = $checklistCount > 0 ? round(($completed / $checklistCount) * 100, 1) : 0;
            $weeklyPerformance['conformityRate'][] = $rate;
        }

        // Current period vs previous period comparison
        $currentPeriodStart = Carbon::parse($startDate);
        $currentPeriodEnd = Carbon::parse($endDate);
        $periodDays = $currentPeriodStart->diffInDays($currentPeriodEnd);

        $previousPeriodStart = (clone $currentPeriodStart)->subDays($periodDays + 1);
        $previousPeriodEnd = (clone $currentPeriodStart)->subDay();

        // Current period stats
        $currentChecklistsQuery = SafetyChecklist::whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd]);
        $currentCleaningsQuery = CleaningControl::whereBetween('cleaning_date', [$currentPeriodStart, $currentPeriodEnd]);

        if ($scopedUnitId) {
            $currentChecklistsQuery->where('unit_id', $scopedUnitId);
            $currentCleaningsQuery->where('unit_id', $scopedUnitId);
        }

        $currentChecklists = $currentChecklistsQuery->count();
        $currentCleanings = $currentCleaningsQuery->count();
        $currentProcedures = $currentChecklists + $currentCleanings;

        // Previous period stats
        $previousChecklistsQuery = SafetyChecklist::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd]);
        $previousCleaningsQuery = CleaningControl::whereBetween('cleaning_date', [$previousPeriodStart, $previousPeriodEnd]);

        if ($scopedUnitId) {
            $previousChecklistsQuery->where('unit_id', $scopedUnitId);
            $previousCleaningsQuery->where('unit_id', $scopedUnitId);
        }

        $previousChecklists = $previousChecklistsQuery->count();
        $previousCleanings = $previousCleaningsQuery->count();
        $previousProcedures = $previousChecklists + $previousCleanings;

        // Session status distribution
        $sessionQuery = SafetyChecklist::whereBetween('created_at', [$startDate, $endDate]);
        if ($scopedUnitId) {
            $sessionQuery->where('unit_id', $scopedUnitId);
        }

        $completedSessions = (clone $sessionQuery)->where('current_phase', 'completed')->count();
        $interruptedSessions = (clone $sessionQuery)->where('current_phase', 'interrupted')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'weeklyPerformance' => $weeklyPerformance,
                'monthlyComparison' => [
                    'categories' => ['Checklists', 'Limpezas', 'Procedimentos'],
                    'current' => [$currentChecklists, $currentCleanings, $currentProcedures],
                    'previous' => [$previousChecklists, $previousCleanings, $previousProcedures]
                ],
                'sessionStatus' => [
                    'labels' => ['Concluído', 'Interrompido'],
                    'series' => [$completedSessions, $interruptedSessions],
                    'total' => $completedSessions + $interruptedSessions
                ],
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]
        ]);
    }
}
