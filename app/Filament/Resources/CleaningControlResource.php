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
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cleaning_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cleaning_time'),
                Tables\Columns\IconColumn::make('daily_cleaning')
                    ->boolean(),
                Tables\Columns\IconColumn::make('weekly_cleaning')
                    ->boolean(),
                Tables\Columns\IconColumn::make('monthly_cleaning')
                    ->boolean(),
                Tables\Columns\IconColumn::make('special_cleaning')
                    ->boolean(),
                Tables\Columns\TextColumn::make('cleaning_products_used')
                    ->searchable(),
                Tables\Columns\IconColumn::make('external_cleaning_done')
                    ->boolean(),
                Tables\Columns\IconColumn::make('internal_cleaning_done')
                    ->boolean(),
                Tables\Columns\IconColumn::make('filter_replacement')
                    ->boolean(),
                Tables\Columns\IconColumn::make('system_disinfection')
                    ->boolean(),
                Tables\Columns\TextColumn::make('responsible_signature')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
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
