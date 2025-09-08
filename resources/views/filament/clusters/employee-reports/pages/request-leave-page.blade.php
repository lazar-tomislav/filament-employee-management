<x-filament-panels::page>


    <div class="w-8/12 mx-auto space-y-5">
        @livewire(\Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets\RequestLeaveWidget::class,['record' => $this->record])

        @if($this->record)
            @livewire(\Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets\AbsenceWidget::class,['absenceType' => 'current', 'record' => $this->record])
            @livewire(\Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets\AbsenceWidget::class,['absenceType' => 'past', 'record' => $this->record])
        @endif
    </div>

</x-filament-panels::page>
