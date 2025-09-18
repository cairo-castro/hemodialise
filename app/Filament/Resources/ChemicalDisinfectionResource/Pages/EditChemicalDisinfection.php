<?php

namespace App\Filament\Resources\ChemicalDisinfectionResource\Pages;

use App\Filament\Resources\ChemicalDisinfectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChemicalDisinfection extends EditRecord
{
    protected static string $resource = ChemicalDisinfectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
