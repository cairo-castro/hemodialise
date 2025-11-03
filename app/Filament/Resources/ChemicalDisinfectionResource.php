<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChemicalDisinfectionResource\Pages;
use App\Filament\Resources\ChemicalDisinfectionResource\RelationManagers;
use App\Models\ChemicalDisinfection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ChemicalDisinfectionResource extends Resource
{
    protected static ?string $model = ChemicalDisinfection::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationLabel = 'Desinfecção Química';

    protected static ?string $modelLabel = 'Desinfecção Química';

    protected static ?string $pluralModelLabel = 'Desinfecções Químicas';

    protected static ?string $navigationGroup = 'Operacional';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Informações Gerais
                Forms\Components\Section::make('Informações Gerais')
                    ->schema([
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
                        Forms\Components\DatePicker::make('disinfection_date')
                            ->label('Data da Desinfecção')
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
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Hora de Início')
                            ->required()
                            ->native(false)
                            ->seconds(false),
                        Forms\Components\TimePicker::make('end_time')
                            ->label('Hora de Término')
                            ->native(false)
                            ->seconds(false),
                    ])
                    ->columns(2),

                // Produto Químico
                Forms\Components\Section::make('Produto Químico')
                    ->schema([
                        Forms\Components\TextInput::make('chemical_product')
                            ->label('Produto Químico')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('batch_number')
                            ->label('Número do Lote')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('expiry_date')
                            ->label('Data de Validade')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\TextInput::make('concentration')
                            ->label('Concentração')
                            ->required()
                            ->numeric()
                            ->step(0.01),
                        Forms\Components\TextInput::make('concentration_unit')
                            ->label('Unidade de Concentração')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('%, ppm, mg/L, etc.'),
                        Forms\Components\TextInput::make('contact_time_minutes')
                            ->label('Tempo de Contato (minutos)')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Parâmetros de Temperatura
                Forms\Components\Section::make('Temperatura')
                    ->schema([
                        Forms\Components\TextInput::make('initial_temperature')
                            ->label('Temperatura Inicial (°C)')
                            ->numeric()
                            ->step(0.1)
                            ->suffix('°C'),
                        Forms\Components\TextInput::make('final_temperature')
                            ->label('Temperatura Final (°C)')
                            ->numeric()
                            ->step(0.1)
                            ->suffix('°C'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Verificações de Procedimento
                Forms\Components\Section::make('Verificações de Procedimento')
                    ->schema([
                        Forms\Components\Select::make('circulation_verified')
                            ->label('Circulação Verificada')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('contact_time_completed')
                            ->label('Tempo de Contato Completo')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('rinse_performed')
                            ->label('Enxágue Realizado')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('system_tested')
                            ->label('Sistema Testado')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('effectiveness_verified')
                            ->label('Eficácia Verificada')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Observações e Assinatura
                Forms\Components\Section::make('Observações e Assinatura')
                    ->schema([
                        Forms\Components\Textarea::make('observations')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('responsible_signature')
                            ->label('Assinatura do Responsável')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('machine.name')
                    ->label('Máquina')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('disinfection_date')
                    ->label('Data da Desinfecção')
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
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Hora Início'),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Hora Fim'),
                Tables\Columns\TextColumn::make('chemical_product')
                    ->label('Produto Químico')
                    ->searchable(),
                Tables\Columns\TextColumn::make('concentration')
                    ->label('Concentração')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_time_minutes')
                    ->label('Tempo de Contato (min)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('effectiveness_verified')
                    ->label('Eficácia Verificada')
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->label('Exportar para Excel')
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename(fn () => 'desinfeccao-quimica-' . now()->format('Y-m-d-His'))
                                ->withWriterType(\Maatwebsite\Excel\Excel::XLSX),
                        ]),
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
            'index' => Pages\ListChemicalDisinfections::route('/'),
            'create' => Pages\CreateChemicalDisinfection::route('/create'),
            'edit' => Pages\EditChemicalDisinfection::route('/{record}/edit'),
        ];
    }
}
