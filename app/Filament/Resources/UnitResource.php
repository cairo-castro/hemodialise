<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Filament\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Unidades';

    protected static ?string $modelLabel = 'Unidade';

    protected static ?string $pluralModelLabel = 'Unidades';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome da Unidade')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label('Código')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                TextInput::make('address')
                    ->label('Endereço')
                    ->required()
                    ->maxLength(500),
                TextInput::make('phone')
                    ->label('Telefone')
                    ->tel()
                    ->maxLength(20),
                TextInput::make('manager_name')
                    ->label('Nome do Gerente')
                    ->maxLength(255),
                Toggle::make('active')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable(),
                TextColumn::make('address')
                    ->label('Endereço')
                    ->limit(50),
                TextColumn::make('phone')
                    ->label('Telefone'),
                TextColumn::make('manager_name')
                    ->label('Gerente'),
                IconColumn::make('active')
                    ->label('Status')
                    ->boolean(),
                TextColumn::make('users_count')
                    ->label('Usuários')
                    ->counts('users'),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status'),
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
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
            'activities' => Pages\ListUnitActivities::route('/{record}/activities'),
        ];
    }
}
