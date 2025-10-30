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
                Tables\Columns\TextColumn::make('patient.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('machine.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('session_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift')
                    ->searchable(),
                Tables\Columns\IconColumn::make('machine_disinfected')
                    ->boolean(),
                Tables\Columns\IconColumn::make('capillary_lines_identified')
                    ->boolean(),
                Tables\Columns\IconColumn::make('patient_identification_confirmed')
                    ->boolean(),
                Tables\Columns\IconColumn::make('vascular_access_evaluated')
                    ->boolean(),
                Tables\Columns\IconColumn::make('vital_signs_checked')
                    ->boolean(),
                Tables\Columns\IconColumn::make('medications_reviewed')
                    ->boolean(),
                Tables\Columns\IconColumn::make('dialyzer_membrane_checked')
                    ->boolean(),
                Tables\Columns\IconColumn::make('equipment_functioning_verified')
                    ->boolean(),
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
            'index' => Pages\ListSafetyChecklists::route('/'),
            'create' => Pages\CreateSafetyChecklist::route('/create'),
            'edit' => Pages\EditSafetyChecklist::route('/{record}/edit'),
        ];
    }
}
