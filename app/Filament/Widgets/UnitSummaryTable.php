<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use App\Models\SafetyChecklist;
use App\Models\CleaningControl;
use App\Models\Machine;
use App\Models\Patient;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UnitSummaryTable extends BaseWidget
{
    protected static ?string $heading = 'Resumo por Unidade';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Unit::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Unidade')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('machines_count')
                    ->label('Máquinas')
                    ->getStateUsing(fn (Unit $record) => Machine::where('unit_id', $record->id)->count())
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('machines_active')
                    ->label('Ativas')
                    ->getStateUsing(fn (Unit $record) => Machine::where('unit_id', $record->id)
                        ->where('active', true)
                        ->count())
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('machines_in_use')
                    ->label('Em Uso')
                    ->getStateUsing(fn (Unit $record) => Machine::where('unit_id', $record->id)
                        ->where('status', 'em_uso')
                        ->count())
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('patients_count')
                    ->label('Pacientes')
                    ->getStateUsing(fn (Unit $record) => Patient::where('unit_id', $record->id)
                        ->where('status', 'ativo')
                        ->count())
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('checklists_today')
                    ->label('Checklists Hoje')
                    ->getStateUsing(function (Unit $record) {
                        return SafetyChecklist::whereHas('machine', function ($query) use ($record) {
                            $query->where('unit_id', $record->id);
                        })
                        ->whereDate('session_date', today())
                        ->count();
                    })
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('cleanings_today')
                    ->label('Limpezas Hoje')
                    ->getStateUsing(function (Unit $record) {
                        return CleaningControl::whereHas('machine', function ($query) use ($record) {
                            $query->where('unit_id', $record->id);
                        })
                        ->whereDate('cleaning_date', today())
                        ->count();
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('address')
                    ->label('Endereço')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name');
    }
}
