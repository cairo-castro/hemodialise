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
                Forms\Components\Select::make('machine_id')
                    ->relationship('machine', 'name')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('disinfection_date')
                    ->required(),
                Forms\Components\TextInput::make('shift')
                    ->required(),
                Forms\Components\TextInput::make('start_time')
                    ->required(),
                Forms\Components\TextInput::make('end_time'),
                Forms\Components\TextInput::make('chemical_product')
                    ->required(),
                Forms\Components\TextInput::make('concentration')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('concentration_unit')
                    ->required(),
                Forms\Components\TextInput::make('contact_time_minutes')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('initial_temperature')
                    ->numeric(),
                Forms\Components\TextInput::make('final_temperature')
                    ->numeric(),
                Forms\Components\Toggle::make('circulation_verified')
                    ->required(),
                Forms\Components\Toggle::make('contact_time_completed')
                    ->required(),
                Forms\Components\Toggle::make('rinse_performed')
                    ->required(),
                Forms\Components\Toggle::make('system_tested')
                    ->required(),
                Forms\Components\TextInput::make('batch_number'),
                Forms\Components\DatePicker::make('expiry_date'),
                Forms\Components\Toggle::make('effectiveness_verified')
                    ->required(),
                Forms\Components\Textarea::make('observations')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('responsible_signature')
                    ->required(),
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
                //
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
