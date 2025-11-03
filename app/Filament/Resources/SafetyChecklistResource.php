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
                // Informações Gerais
                Forms\Components\Section::make('Informações Gerais')
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->label('Paciente')
                            ->relationship('patient', 'full_name')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('machine_id')
                            ->label('Máquina')
                            ->relationship('machine', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->label('Responsável')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\DatePicker::make('session_date')
                            ->label('Data da Sessão')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\Select::make('shift')
                            ->label('Turno')
                            ->options([
                                'manha' => 'Manhã',
                                'tarde' => 'Tarde',
                                'noite' => 'Noite',
                                'madrugada' => 'Madrugada',
                            ])
                            ->required(),
                        Forms\Components\Select::make('current_phase')
                            ->label('Fase Atual')
                            ->options([
                                'pre_dialysis' => 'Pré-Diálise',
                                'during_session' => 'Durante Sessão',
                                'post_dialysis' => 'Pós-Diálise',
                                'completed' => 'Completo',
                                'interrupted' => 'Interrompido',
                            ])
                            ->default('pre_dialysis'),
                    ])
                    ->columns(2),

                // Pré-Diálise
                Forms\Components\Section::make('Pré-Diálise')
                    ->schema([
                        Forms\Components\Select::make('machine_disinfected')
                            ->label('Máquina desinfetada')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('capillary_lines_identified')
                            ->label('Linhas capilares identificadas')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('reagent_test_performed')
                            ->label('Teste de reagente realizado')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('pressure_sensors_verified')
                            ->label('Sensores de pressão verificados')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('air_bubble_detector_verified')
                            ->label('Detector de bolhas verificado')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('patient_identification_confirmed')
                            ->label('Identificação do paciente confirmada')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('vascular_access_evaluated')
                            ->label('Acesso vascular avaliado')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('av_fistula_arm_washed')
                            ->label('Braço da fístula lavado')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('patient_weighed')
                            ->label('Paciente pesado')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('vital_signs_checked')
                            ->label('Sinais vitais verificados')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('medications_reviewed')
                            ->label('Medicações revisadas')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('dialyzer_membrane_checked')
                            ->label('Membrana do dialisador verificada')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('equipment_functioning_verified')
                            ->label('Funcionamento do equipamento verificado')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Durante a Sessão
                Forms\Components\Section::make('Durante a Sessão')
                    ->schema([
                        Forms\Components\Select::make('dialysis_parameters_verified')
                            ->label('Parâmetros de diálise verificados')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('heparin_double_checked')
                            ->label('Heparina duplamente verificada')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('antisepsis_performed')
                            ->label('Antissepsia realizada')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('vascular_access_monitored')
                            ->label('Acesso vascular monitorado')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('vital_signs_monitored_during')
                            ->label('Sinais vitais monitorados')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('patient_comfort_assessed')
                            ->label('Conforto do paciente avaliado')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('fluid_balance_monitored')
                            ->label('Balanço hídrico monitorado')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('alarms_responded')
                            ->label('Alarmes atendidos')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Pós-Diálise
                Forms\Components\Section::make('Pós-Diálise')
                    ->schema([
                        Forms\Components\Select::make('session_completed_safely')
                            ->label('Sessão concluída com segurança')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('vascular_access_secured')
                            ->label('Acesso vascular protegido')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('patient_vital_signs_stable')
                            ->label('Sinais vitais estáveis')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('complications_assessed')
                            ->label('Complicações avaliadas')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('equipment_cleaned')
                            ->label('Equipamento limpo')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Observações
                Forms\Components\Section::make('Observações e Incidentes')
                    ->schema([
                        Forms\Components\Textarea::make('observations')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('incidents')
                            ->label('Incidentes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
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
                Tables\Filters\SelectFilter::make('unit_id')
                    ->label('Unidade')
                    ->relationship('machine.unit', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('shift')
                    ->label('Turno')
                    ->options([
                        'manha' => 'Manhã',
                        'tarde' => 'Tarde',
                        'noite' => 'Noite',
                        'madrugada' => 'Madrugada',
                    ]),
                Tables\Filters\SelectFilter::make('current_phase')
                    ->label('Fase')
                    ->options([
                        'pre_dialysis' => 'Pré-Diálise',
                        'during_session' => 'Durante Sessão',
                        'post_dialysis' => 'Pós-Diálise',
                        'completed' => 'Completo',
                        'interrupted' => 'Interrompido',
                    ]),
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
