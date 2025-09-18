<?php

namespace App\Filament\Resources\ChemicalDisinfectionResource\Pages;

use App\Filament\Resources\ChemicalDisinfectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChemicalDisinfections extends ListRecords
{
    protected static string $resource = ChemicalDisinfectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
