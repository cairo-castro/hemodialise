<?php

namespace App\Filament\Widgets;

use App\Models\Machine;
use App\Models\Unit;
use Filament\Widgets\ChartWidget;

class MachineStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Status das Máquinas';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = 'all';

    protected function getFilters(): ?array
    {
        $filters = ['all' => 'Todas as Unidades'];

        $units = Unit::orderBy('name')->get();

        foreach ($units as $unit) {
            $filters[$unit->id] = $unit->name;
        }

        return $filters;
    }

    protected function getData(): array
    {
        $query = Machine::query();

        if ($this->filter !== 'all') {
            $query->where('unit_id', $this->filter);
        }

        $disponivel = $query->clone()->where('status', 'disponivel')->count();
        $emUso = $query->clone()->where('status', 'em_uso')->count();
        $manutencao = $query->clone()->where('status', 'manutencao')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Máquinas',
                    'data' => [$disponivel, $emUso, $manutencao],
                    'backgroundColor' => [
                        '#10b981', // Verde - Disponível
                        '#f59e0b', // Amarelo - Em Uso
                        '#ef4444', // Vermelho - Manutenção
                    ],
                ],
            ],
            'labels' => ['Disponível', 'Em Uso', 'Manutenção'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
