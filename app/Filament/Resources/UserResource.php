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

    protected static ?string $navigationLabel = 'Usuários';

    protected static ?string $modelLabel = 'Usuário';

    protected static ?string $pluralModelLabel = 'Usuários';

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
                Forms\Components\Checkbox::make('is_global')
                    ->label('Usuário Global (acesso a todas as unidades)')
                    ->helperText('Usuários globais não são associados a uma unidade específica')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            // Se global, limpar unit_id
                            $set('unit_id', null);
                        }
                    })
                    ->default(false),
                Forms\Components\Select::make('unit_id')
                    ->relationship('unit', 'name')
                    ->label('Unidade Principal')
                    ->helperText('Unidade principal do usuário. Deixe em branco para usuários globais.')
                    ->searchable()
                    ->preload()
                    ->hidden(fn (callable $get) => $get('is_global') === true)
                    ->required(fn (callable $get) => $get('is_global') !== true),
                Forms\Components\Select::make('units')
                    ->relationship('units', 'name')
                    ->label('Unidades Adicionais')
                    ->helperText('Outras unidades que o usuário pode acessar (além da principal)')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->hidden(fn (callable $get) => $get('is_global') === true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->visibleOn('create')
                    ->label('Senha'),

                // Alteração de senha (somente na edição)
                Forms\Components\Section::make('Alterar Senha')
                    ->schema([
                        Forms\Components\TextInput::make('new_password')
                            ->password()
                            ->label('Nova Senha')
                            ->helperText('Deixe em branco para manter a senha atual')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->minLength(6),
                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->password()
                            ->label('Confirmar Nova Senha')
                            ->same('new_password')
                            ->dehydrated(false),
                    ])
                    ->visibleOn('edit')
                    ->collapsible()
                    ->collapsed(),
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
                    ->default('Global')
                    ->badge()
                    ->color(fn ($record) => $record->unit_id === null ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('units.name')
                    ->label('Unidades Adicionais')
                    ->badge()
                    ->separator(',')
                    ->default('-')
                    ->toggleable(),
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
            'activities' => Pages\ListUserActivities::route('/{record}/activities'),
        ];
    }
}