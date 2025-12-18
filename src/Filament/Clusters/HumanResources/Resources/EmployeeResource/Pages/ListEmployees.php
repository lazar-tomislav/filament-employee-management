<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;


    public function mount(): void
    {
        if(auth()->user()->isEmployee()){
            redirect()->to(EmployeeResource::getUrl('view',['record'=>auth()->user()->employee->id]));
        }
    }

    public function getHeading(): string | Htmlable
    {
     return "Zaposlenici";
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->modalHeading("Dodaj zaposlenika"),
        ];
    }
}
