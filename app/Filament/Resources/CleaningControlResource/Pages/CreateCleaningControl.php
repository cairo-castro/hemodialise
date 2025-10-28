<?php

namespace App\Filament\Resources\CleaningControlResource\Pages;

use App\Filament\Resources\CleaningControlResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCleaningControl extends CreateRecord
{
    protected static string $resource = CleaningControlResource::class;

    /**
     * Preenche unit_id explicitamente da máquina (performance: zero overhead)
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $machine = \App\Models\Machine::find($data['machine_id']);
        $data['unit_id'] = $machine->unit_id;

        return $data;
    }
}
