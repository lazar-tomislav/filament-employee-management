<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\TimeLogResource\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\TimeLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimeLogs extends ListRecords
{
    protected static string $resource = TimeLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver(),
        ];
    }
}
