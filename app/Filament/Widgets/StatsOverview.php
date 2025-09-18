<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Unit;
use App\Models\SafetyChecklist;
use App\Models\Machine;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Usuários Ativos', User::where('role', '!=', 'admin')->count())
                ->description('Total de usuários no sistema')
                ->color('success')
                ->icon('heroicon-o-users'),

            Stat::make('Unidades', Unit::where('active', true)->count())
                ->description('Centros de hemodiálise ativos')
                ->color('primary')
                ->icon('heroicon-o-building-office'),

            Stat::make('Checklists Hoje', SafetyChecklist::whereDate('session_date', today())->count())
                ->description('Realizados hoje')
                ->color('warning')
                ->icon('heroicon-o-clipboard-document-check'),

            Stat::make('Máquinas', Machine::count())
                ->description('Total de equipamentos')
                ->color('info')
                ->icon('heroicon-o-cpu-chip'),
        ];
    }
}