<?php

namespace App\Filament\Resources\ActivityResource\Pages;

use App\Filament\Resources\ActivityResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\KeyValueEntry;

class ViewActivity extends ViewRecord
{
    protected static string $resource = ActivityResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informações da Atividade')
                    ->schema([
                        TextEntry::make('log_name')
                            ->label('Tipo de Log'),
                        TextEntry::make('description')
                            ->label('Evento')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'created' => 'success',
                                'updated' => 'warning',
                                'deleted' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'created' => 'Criado',
                                'updated' => 'Atualizado',
                                'deleted' => 'Excluído',
                                default => ucfirst($state),
                            }),
                        TextEntry::make('subject_type')
                            ->label('Tipo de Modelo')
                            ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-'),
                        TextEntry::make('subject_id')
                            ->label('ID do Registro'),
                        TextEntry::make('causer.name')
                            ->label('Usuário Responsável')
                            ->default('Sistema'),
                        TextEntry::make('created_at')
                            ->label('Data e Hora')
                            ->dateTime('d/m/Y H:i:s'),
                    ])
                    ->columns(2),
                Section::make('Alterações')
                    ->schema([
                        KeyValueEntry::make('properties.attributes')
                            ->label('Novos Valores')
                            ->visible(fn ($record) => $record->properties && isset($record->properties['attributes'])),
                        KeyValueEntry::make('properties.old')
                            ->label('Valores Anteriores')
                            ->visible(fn ($record) => $record->properties && isset($record->properties['old'])),
                    ])
                    ->collapsed(false),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
