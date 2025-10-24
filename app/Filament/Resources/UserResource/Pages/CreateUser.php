<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove is_global (não é um campo do banco)
        unset($data['is_global']);

        return $data;
    }

    protected function afterCreate(): void
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

        if (!empty($syncData)) {
            $this->record->units()->sync($syncData);
        }
    }
}