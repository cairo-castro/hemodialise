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

class ChemicalDisinfectionResource extends Resource
{
    protected static ?string $model = ChemicalDisinfection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('disinfection_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('end_time'),
                Tables\Columns\TextColumn::make('chemical_product')
                    ->searchable(),
                Tables\Columns\TextColumn::make('concentration')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('concentration_unit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_time_minutes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('initial_temperature')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('final_temperature')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('circulation_verified')
                    ->boolean(),
                Tables\Columns\IconColumn::make('contact_time_completed')
                    ->boolean(),
                Tables\Columns\IconColumn::make('rinse_performed')
                    ->boolean(),
                Tables\Columns\IconColumn::make('system_tested')
                    ->boolean(),
                Tables\Columns\TextColumn::make('batch_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('effectiveness_verified')
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
            'index' => Pages\ListChemicalDisinfections::route('/'),
            'create' => Pages\CreateChemicalDisinfection::route('/create'),
            'edit' => Pages\EditChemicalDisinfection::route('/{record}/edit'),
        ];
    }
}
