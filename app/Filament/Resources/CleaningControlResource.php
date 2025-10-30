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
                Forms\Components\Select::make('machine_id')
                    ->relationship('machine', 'name')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('cleaning_date')
                    ->required(),
                Forms\Components\TextInput::make('shift')
                    ->required(),
                Forms\Components\TextInput::make('cleaning_time')
                    ->required(),
                Forms\Components\Toggle::make('daily_cleaning')
                    ->required(),
                Forms\Components\Toggle::make('weekly_cleaning')
                    ->required(),
                Forms\Components\Toggle::make('monthly_cleaning')
                    ->required(),
                Forms\Components\Toggle::make('special_cleaning')
                    ->required(),
                Forms\Components\TextInput::make('cleaning_products_used'),
                Forms\Components\Textarea::make('cleaning_procedure')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('external_cleaning_done')
                    ->required(),
                Forms\Components\Toggle::make('internal_cleaning_done')
                    ->required(),
                Forms\Components\Toggle::make('filter_replacement')
                    ->required(),
                Forms\Components\Toggle::make('system_disinfection')
                    ->required(),
                Forms\Components\Textarea::make('observations')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('responsible_signature'),
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
