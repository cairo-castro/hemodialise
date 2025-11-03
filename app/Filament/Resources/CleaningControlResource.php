<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CleaningControlResource\Pages;
use App\Filament\Resources\CleaningControlResource\RelationManagers;
use App\Models\CleaningControl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class CleaningControlResource extends Resource
{
    protected static ?string $model = CleaningControl::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Controle de Limpeza';

    protected static ?string $modelLabel = 'Controle de Limpeza';

    protected static ?string $pluralModelLabel = 'Controles de Limpeza';

    protected static ?string $navigationGroup = 'Operacional';

    protected static ?int $navigationSort = 2;

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
                        Forms\Components\DatePicker::make('cleaning_date')
                            ->label('Data da Limpeza')
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
                        Forms\Components\TimePicker::make('cleaning_time')
                            ->label('Horário da Limpeza')
                            ->required()
                            ->native(false)
                            ->seconds(false),
                        Forms\Components\TextInput::make('responsible_signature')
                            ->label('Assinatura do Responsável')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                // Tipo de Limpeza
                Forms\Components\Section::make('Tipo de Limpeza')
                    ->schema([
                        Forms\Components\Select::make('daily_cleaning')
                            ->label('Limpeza Diária')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('weekly_cleaning')
                            ->label('Limpeza Semanal')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('monthly_cleaning')
                            ->label('Limpeza Mensal')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('special_cleaning')
                            ->label('Limpeza Especial')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Itens de Limpeza
                Forms\Components\Section::make('Itens de Limpeza')
                    ->schema([
                        Forms\Components\Select::make('hd_machine_cleaning')
                            ->label('Limpeza da Máquina de HD')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('osmosis_cleaning')
                            ->label('Limpeza da Osmose')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('serum_support_cleaning')
                            ->label('Limpeza do Suporte de Soro')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('external_cleaning_done')
                            ->label('Limpeza Externa Realizada')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('internal_cleaning_done')
                            ->label('Limpeza Interna Realizada')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('filter_replacement')
                            ->label('Troca de Filtro')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('system_disinfection')
                            ->label('Desinfecção do Sistema')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('chemical_disinfection')
                            ->label('Desinfecção Química')
                            ->options([
                                true => 'Conforme',
                                false => 'Não Conforme',
                                null => 'Não se Aplica',
                            ])
                            ->native(false),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Produtos e Procedimentos
                Forms\Components\Section::make('Produtos e Procedimentos')
                    ->schema([
                        Forms\Components\TextInput::make('cleaning_products_used')
                            ->label('Produtos de Limpeza Utilizados')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('cleaning_procedure')
                            ->label('Procedimento de Limpeza')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('observations')
                            ->label('Observações')
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
                Tables\Columns\TextColumn::make('machine.name')
                    ->label('Máquina')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cleaning_date')
                    ->label('Data da Limpeza')
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
                Tables\Columns\TextColumn::make('cleaning_time')
                    ->label('Horário'),
                Tables\Columns\IconColumn::make('daily_cleaning')
                    ->label('Limpeza Diária')
                    ->boolean(),
                Tables\Columns\IconColumn::make('weekly_cleaning')
                    ->label('Limpeza Semanal')
                    ->boolean(),
                Tables\Columns\IconColumn::make('monthly_cleaning')
                    ->label('Limpeza Mensal')
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
                                ->withFilename(fn () => 'controle-limpeza-' . now()->format('Y-m-d-His'))
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
            'index' => Pages\ListCleaningControls::route('/'),
            'create' => Pages\CreateCleaningControl::route('/create'),
            'edit' => Pages\EditCleaningControl::route('/{record}/edit'),
        ];
    }
}
