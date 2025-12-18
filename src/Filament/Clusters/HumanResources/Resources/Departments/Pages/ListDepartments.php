<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\Departments\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\DepartmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListDepartments extends ListRecords
{
    protected static string $resource = DepartmentResource::class;
    protected Width|string|null $maxContentWidth="full";

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
