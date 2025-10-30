<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use Spatie\Activitylog\Models\Activity;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Log de Atividades';

    protected static ?string $modelLabel = 'Atividade';

    protected static ?string $pluralModelLabel = 'Atividades';

    protected static ?string $navigationGroup = 'Administração';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Tipo')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Evento')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created' => 'Criado',
                        'updated' => 'Atualizado',
                        'deleted' => 'Excluído',
                        default => ucfirst($state),
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Modelo')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label('ID do Registro')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Usuário')
                    ->default('Sistema')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->default('-'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('description')
                    ->label('Evento')
                    ->options([
                        'created' => 'Criado',
                        'updated' => 'Atualizado',
                        'deleted' => 'Excluído',
                    ]),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->label('Modelo')
                    ->options([
                        'App\Models\User' => 'Usuário',
                        'App\Models\Unit' => 'Unidade',
                        'App\Models\Machine' => 'Máquina',
                        'App\Models\Patient' => 'Paciente',
                        'App\Models\SafetyChecklist' => 'Checklist de Segurança',
                        'App\Models\CleaningControl' => 'Controle de Limpeza',
                        'App\Models\ChemicalDisinfection' => 'Desinfecção Química',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('De'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'view' => Pages\ViewActivity::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
