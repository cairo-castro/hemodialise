<?php

namespace App\Filament\Resources\CleaningControlResource\Pages;

use App\Filament\Resources\CleaningControlResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCleaningControl extends EditRecord
{
    protected static string $resource = CleaningControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
