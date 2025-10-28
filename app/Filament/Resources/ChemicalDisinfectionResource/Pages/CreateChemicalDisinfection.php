<?php

namespace App\Filament\Resources\ChemicalDisinfectionResource\Pages;

use App\Filament\Resources\ChemicalDisinfectionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateChemicalDisinfection extends CreateRecord
{
    protected static string $resource = ChemicalDisinfectionResource::class;

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
