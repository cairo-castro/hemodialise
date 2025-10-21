<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Administração';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nome'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->label('E-mail'),
                Forms\Components\Select::make('role')
                    ->options([
                        'admin' => 'Administrador',
                        'gestor' => 'Gestor',
                        'coordenador' => 'Coordenador',
                        'supervisor' => 'Supervisor',
                        'tecnico' => 'Técnico',
                    ])
                    ->required()
                    ->label('Função'),
                Forms\Components\Select::make('unit_id')
                    ->relationship('unit', 'name')
                    ->label('Unidade Principal')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->visibleOn('create')
                    ->label('Senha'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nome'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label('E-mail'),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->colors([
                        'primary' => 'admin',
                        'success' => 'gestor',
                        'warning' => 'coordenador',
                        'info' => 'supervisor',
                        'gray' => 'tecnico',
                    ])
                    ->searchable()
                    ->sortable()
                    ->label('Função'),
                Tables\Columns\TextColumn::make('unit.name')
                    ->label('Unidade Principal')
                    ->searchable()
                    ->sortable()
                    ->default('Sem Unidade'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Criado em'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit')
                    ->relationship('unit', 'name')
                    ->label('Unidade'),
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'Administrador',
                        'gestor' => 'Gestor',
                        'coordenador' => 'Coordenador',
                        'supervisor' => 'Supervisor',
                        'tecnico' => 'Técnico',
                    ])
                    ->label('Função'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}