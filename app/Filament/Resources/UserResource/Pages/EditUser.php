<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Adicionar is_global baseado em unit_id
        $data['is_global'] = $this->record->unit_id === null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove is_global (não é um campo do banco)
        unset($data['is_global']);

        // Se new_password foi preenchido, atualizar senha
        if (isset($data['new_password']) && filled($data['new_password'])) {
            $data['password'] = $data['new_password'];
        }

        // Remover campos temporários de senha
        unset($data['new_password']);
        unset($data['new_password_confirmation']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Sincronizar unidades adicionais
        $additionalUnits = $this->form->getState()['units'] ?? [];

        // Preparar array para sincronização com is_primary
        $syncData = [];

        // Se unit_id está definido, marcar como principal
        if ($this->record->unit_id) {
            $syncData[$this->record->unit_id] = ['is_primary' => true];
        }

        // Adicionar unidades adicionais (não são principais)
        foreach ($additionalUnits as $unitId) {
            if ($unitId != $this->record->unit_id) {
                $syncData[$unitId] = ['is_primary' => false];
            }
        }

        // Sempre sincronizar (mesmo que vazio para usuários globais)
        $this->record->units()->sync($syncData);
    }
}