<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\TimeLogResource\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\TimeLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTimeLog extends CreateRecord
{
    protected static string $resource = TimeLogResource::class;
}
