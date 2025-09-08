<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class RequestLeavePage extends Page
{
    use HasPageShield;

    protected static ?string $cluster = HumanResources::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $title = 'Zatraži odsustvo';

    public static function getNavigationLabel(): string
    {
        return "Zatraži odsustvo";
    }

    protected ?Employee $record = null;

    protected string $view = 'filament-employee-management::filament.clusters.human-resources.pages.request-leave-page';

    public function mount(): void
    {
        if(auth()->user()->isEmployee()){
            $this->record = auth()->user()->employee;
        }elseif($recordUrl = (int)request()->input('record')){
            $this->record = Employee::find($recordUrl);
        }else{
            // if the user is not employee, we can set the record to null or handle it accordingly
            $this->record = null;
        }
    }
}
