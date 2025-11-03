<?php

namespace App\Filament\Widgets;

use App\Models\CleaningControl;
use App\Models\ChemicalDisinfection;
use App\Models\SafetyChecklist;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OperationalActivitiesChart extends ChartWidget
{
    protected static ?string $heading = 'Atividades Operacionais (Últimos 7 Dias)';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(function ($daysAgo) {
            return now()->subDays($daysAgo)->format('Y-m-d');
        });

        $checklistsData = [];
        $cleaningsData = [];
        $disinfectionsData = [];
        $labels = [];

        foreach ($days as $day) {
            $labels[] = now()->parse($day)->format('d/m');

            $checklistsData[] = SafetyChecklist::whereDate('session_date', $day)->count();
            $cleaningsData[] = CleaningControl::whereDate('cleaning_date', $day)->count();
            $disinfectionsData[] = ChemicalDisinfection::whereDate('disinfection_date', $day)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Checklists de Segurança',
                    'data' => $checklistsData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Controles de Limpeza',
                    'data' => $cleaningsData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Desinfecções Químicas',
                    'data' => $disinfectionsData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
