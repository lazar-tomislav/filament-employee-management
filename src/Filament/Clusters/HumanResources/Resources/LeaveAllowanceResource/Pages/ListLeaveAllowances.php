<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveAllowanceResource\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveAllowanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLeaveAllowances extends ListRecords
{
    protected static string $resource = LeaveAllowanceResource::class;

    protected static ?string $title="Godišnji odmori zaposlenika";
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
