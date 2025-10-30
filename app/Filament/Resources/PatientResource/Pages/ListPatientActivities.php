<?php

namespace App\Filament\Resources\PatientResource\Pages;

use App\Filament\Resources\PatientResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListPatientActivities extends ListActivities
{
    protected static string $resource = PatientResource::class;
}
