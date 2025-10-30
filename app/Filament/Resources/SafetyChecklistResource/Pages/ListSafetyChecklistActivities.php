<?php

namespace App\Filament\Resources\SafetyChecklistResource\Pages;

use App\Filament\Resources\SafetyChecklistResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListSafetyChecklistActivities extends ListActivities
{
    protected static string $resource = SafetyChecklistResource::class;
}
