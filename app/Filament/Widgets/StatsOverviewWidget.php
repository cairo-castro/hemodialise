<?php

namespace App\Filament\Widgets;

use App\Models\SafetyChecklist;
use App\Models\CleaningControl;
use App\Models\ChemicalDisinfection;
use App\Models\Machine;
use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = 'today';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hoje',
            'week' => 'Esta Semana',
            'month' => 'Este Mês',
            'year' => 'Este Ano',
        ];
    }

    protected function getStats(): array
    {
        $dateRange = $this->getDateRange();

        return [
            Stat::make('Checklists Realizados', SafetyChecklist::whereBetween('session_date', $dateRange)->count())
                ->description('Total de checklists de segurança')
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->chart($this->getChecklistTrend()),

            Stat::make('Limpezas Realizadas', CleaningControl::whereBetween('cleaning_date', $dateRange)->count())
                ->description('Controles de limpeza registrados')
                ->descriptionIcon('heroicon-o-sparkles')
                ->color('info')
                ->chart($this->getCleaningTrend()),

            Stat::make('Desinfecções Químicas', ChemicalDisinfection::whereBetween('disinfection_date', $dateRange)->count())
                ->description('Desinfecções químicas realizadas')
                ->descriptionIcon('heroicon-o-beaker')
                ->color('warning')
                ->chart($this->getDisinfectionTrend()),

            Stat::make('Máquinas Ativas', Machine::where('active', true)->count())
                ->description(Machine::where('status', 'em_uso')->count() . ' em uso no momento')
                ->descriptionIcon('heroicon-o-computer-desktop')
                ->color('primary'),

            Stat::make('Pacientes Ativos', Patient::where('status', 'ativo')->count())
                ->description('Pacientes em tratamento')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Taxa de Conformidade', $this->getComplianceRate() . '%')
                ->description('Checklists sem não conformidades')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color($this->getComplianceRate() >= 90 ? 'success' : ($this->getComplianceRate() >= 70 ? 'warning' : 'danger')),
        ];
    }

    protected function getDateRange(): array
    {
        return match ($this->filter) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    protected function getChecklistTrend(): array
    {
        return SafetyChecklist::query()
            ->where('session_date', '>=', now()->subDays(7))
            ->groupBy(DB::raw('DATE(session_date)'))
            ->selectRaw('DATE(session_date) as date, COUNT(*) as count')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }

    protected function getCleaningTrend(): array
    {
        return CleaningControl::query()
            ->where('cleaning_date', '>=', now()->subDays(7))
            ->groupBy(DB::raw('DATE(cleaning_date)'))
            ->selectRaw('DATE(cleaning_date) as date, COUNT(*) as count')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }

    protected function getDisinfectionTrend(): array
    {
        return ChemicalDisinfection::query()
            ->where('disinfection_date', '>=', now()->subDays(7))
            ->groupBy(DB::raw('DATE(disinfection_date)'))
            ->selectRaw('DATE(disinfection_date) as date, COUNT(*) as count')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }

    protected function getComplianceRate(): float
    {
        $dateRange = $this->getDateRange();

        $total = SafetyChecklist::whereBetween('session_date', $dateRange)->count();

        if ($total === 0) {
            return 100;
        }

        // Contar checklists onde todos os itens obrigatórios estão conformes
        $compliant = SafetyChecklist::whereBetween('session_date', $dateRange)
            ->where('machine_disinfected', true)
            ->where('patient_identification_confirmed', true)
            ->where('vital_signs_checked', true)
            ->count();

        return round(($compliant / $total) * 100, 1);
    }
}
