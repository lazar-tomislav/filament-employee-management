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

    public function getView(): string
    {
        return 'filament-employee-management::filament.clusters.human-resources.pages.request-leave-page';
    }

    public function mount(): void
    {
        // TODO: if the auth user is employee, check if the record is set in url
        $recordUrl = (int)request()->input('record');
        if($recordUrl){
            $this->record = Employee::find($recordUrl);
        }
    }

}
