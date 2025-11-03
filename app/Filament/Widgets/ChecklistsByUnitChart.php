<?php

namespace App\Filament\Widgets;

use App\Models\SafetyChecklist;
use App\Models\Unit;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ChecklistsByUnitChart extends ChartWidget
{
    protected static ?string $heading = 'Checklists por Unidade';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Esta Semana',
            'month' => 'Este Mês',
            'quarter' => 'Este Trimestre',
            'year' => 'Este Ano',
        ];
    }

    protected function getData(): array
    {
        $dateRange = $this->getDateRange();

        $units = Unit::withCount(['machines' => function ($query) use ($dateRange) {
            $query->whereHas('safetyChecklists', function ($q) use ($dateRange) {
                $q->whereBetween('session_date', $dateRange);
            });
        }])->get();

        $data = [];
        $labels = [];
        $colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

        foreach ($units as $index => $unit) {
            $count = SafetyChecklist::whereHas('machine', function ($query) use ($unit) {
                $query->where('unit_id', $unit->id);
            })
            ->whereBetween('session_date', $dateRange)
            ->count();

            $labels[] = $unit->name;
            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Checklists Realizados',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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

    protected function getDateRange(): array
    {
        return match ($this->filter) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}
