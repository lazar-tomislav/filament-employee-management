<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\TimeLogResource\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\TimeLogResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListTimeLogs extends ListRecords
{
    protected static string $resource = TimeLogResource::class;

    public function getTitle(): string|Htmlable
    {
        return "Unosi vremena";
    }

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make()
//                ->slideOver(),
        ];
    }
}
