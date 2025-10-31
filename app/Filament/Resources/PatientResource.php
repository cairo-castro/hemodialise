<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Filament\Resources\PatientResource\RelationManagers;
use App\Models\Patient;
use App\Enums\PatientStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Pacientes';

    protected static ?string $modelLabel = 'Paciente';

    protected static ?string $pluralModelLabel = 'Pacientes';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('full_name')
                    ->required()
                    ->label('Nome Completo'),
                Forms\Components\DatePicker::make('birth_date')
                    ->required()
                    ->label('Data de Nascimento'),
                Forms\Components\Select::make('blood_group')
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'AB' => 'AB',
                        'O' => 'O',
                    ])
                    ->label('Tipo Sanguíneo'),
                Forms\Components\Select::make('rh_factor')
                    ->options([
                        '+' => 'Positivo (+)',
                        '-' => 'Negativo (-)',
                    ])
                    ->label('Fator RH'),
                Forms\Components\Textarea::make('allergies')
                    ->label('Alergias')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('observations')
                    ->label('Observações')
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        PatientStatus::ATIVO->value => PatientStatus::ATIVO->label(),
                        PatientStatus::INATIVO->value => PatientStatus::INATIVO->label(),
                        PatientStatus::TRANSFERIDO->value => PatientStatus::TRANSFERIDO->label(),
                        PatientStatus::ALTA->value => PatientStatus::ALTA->label(),
                        PatientStatus::OBITO->value => PatientStatus::OBITO->label(),
                    ])
                    ->default(PatientStatus::ATIVO->value)
                    ->required()
                    ->label('Status')
                    ->helperText('Status atual do paciente no serviço de hemodiálise'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Data de Nascimento')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('blood_type')
                    ->label('Tipo Sanguíneo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('blood_group')
                    ->label('Grupo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rh_factor')
                    ->label('RH')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PatientStatus::from($state)->label())
                    ->color(fn (string $state): string => PatientStatus::from($state)->color())
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        PatientStatus::ATIVO->value => PatientStatus::ATIVO->label(),
                        PatientStatus::INATIVO->value => PatientStatus::INATIVO->label(),
                        PatientStatus::TRANSFERIDO->value => PatientStatus::TRANSFERIDO->label(),
                        PatientStatus::ALTA->value => PatientStatus::ALTA->label(),
                        PatientStatus::OBITO->value => PatientStatus::OBITO->label(),
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('exclude_terminal')
                    ->label('Excluir Status Terminal (Alta/Óbito)')
                    ->query(fn (Builder $query): Builder => $query->excludeTerminal())
                    ->toggle(),
                Tables\Filters\Filter::make('can_have_sessions')
                    ->label('Apenas Pacientes Ativos para Sessão')
                    ->query(fn (Builder $query): Builder => $query->canHaveSessions())
                    ->toggle(),
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
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
            'activities' => Pages\ListPatientActivities::route('/{record}/activities'),
        ];
    }
}
