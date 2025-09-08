<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Actions;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class LeaveRequestAction
{
    public static function createLeaveRequest(Employee $employee):Action
    {
        return \Filament\Actions\Action::make('createLeaveRequest')
            ->icon(Heroicon::OutlinedSun)
            ->label('Zatraži godišnji odmor')
            ->slideOver()
            ->link()
            ->url(fn() => EmployeeResource::getUrl('view',['record' => $employee->id,'tab'=>'absence']));
    }

}
