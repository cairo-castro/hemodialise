<?php

namespace App\Filament\Resources\CleaningControlResource\Pages;

use App\Filament\Resources\CleaningControlResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCleaningControls extends ListRecords
{
    protected static string $resource = CleaningControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
