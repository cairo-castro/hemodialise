<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SafetyChecklistResource\Pages;
use App\Filament\Resources\SafetyChecklistResource\RelationManagers;
use App\Models\SafetyChecklist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use App\Exports\SafetyChecklistExport;
use Maatwebsite\Excel\Facades\Excel;

class SafetyChecklistResource extends Resource
{
    protected static ?string $model = SafetyChecklist::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Checklists de Segurança';

    protected static ?string $modelLabel = 'Checklist de Segurança';

    protected static ?string $pluralModelLabel = 'Checklists de Segurança';

    protected static ?string $navigationGroup = 'Operacional';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('patient_id')
                    ->relationship('patient', 'id')
                    ->required(),
                Forms\Components\Select::make('machine_id')
                    ->relationship('machine', 'name')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('session_date')
                    ->required(),
                Forms\Components\TextInput::make('shift')
                    ->required(),
                Forms\Components\Toggle::make('machine_disinfected')
                    ->required(),
                Forms\Components\Toggle::make('capillary_lines_identified')
                    ->required(),
                Forms\Components\Toggle::make('patient_identification_confirmed')
                    ->required(),
                Forms\Components\Toggle::make('vascular_access_evaluated')
                    ->required(),
                Forms\Components\Toggle::make('vital_signs_checked')
                    ->required(),
                Forms\Components\Toggle::make('medications_reviewed')
                    ->required(),
                Forms\Components\Toggle::make('dialyzer_membrane_checked')
                    ->required(),
                Forms\Components\Toggle::make('equipment_functioning_verified')
                    ->required(),
                Forms\Components\Textarea::make('observations')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('incidents')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('Paciente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('machine.name')
                    ->label('Máquina')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('session_date')
                    ->label('Data da Sessão')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift')
                    ->label('Turno')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manha' => 'success',
                        'tarde' => 'warning',
                        'noite' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'manha' => 'Manhã',
                        'tarde' => 'Tarde',
                        'noite' => 'Noite',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_phase')
                    ->label('Fase Atual')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pre_dialysis' => 'warning',
                        'during_session' => 'info',
                        'post_dialysis' => 'success',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pre_dialysis' => 'Pré-Diálise',
                        'during_session' => 'Durante Sessão',
                        'post_dialysis' => 'Pós-Diálise',
                        'completed' => 'Completo',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_interrupted')
                    ->label('Interrompido')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export')
                        ->label('Exportar para Excel (Formato Padrão)')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function ($records) {
                            $filename = 'checklist-seguranca-' . now()->format('Y-m-d-His') . '.xlsx';
                            return Excel::download(new SafetyChecklistExport($records), $filename);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSafetyChecklists::route('/'),
            'create' => Pages\CreateSafetyChecklist::route('/create'),
            'edit' => Pages\EditSafetyChecklist::route('/{record}/edit'),
            'activities' => Pages\ListSafetyChecklistActivities::route('/{record}/activities'),
        ];
    }
}
