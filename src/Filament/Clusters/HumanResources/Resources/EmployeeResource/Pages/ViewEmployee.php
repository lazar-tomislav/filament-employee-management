<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Actions\EmployeeAction;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas\EmployeeInfolist;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Url;

class ViewEmployee extends Page implements HasSchemas
{
    use InteractsWithRecord;
    use InteractsWithSchemas;

    #[Url(as: 'tab', keep: true)]
    public string $activeTab = 'info';

    protected static string $resource = EmployeeResource::class;

    protected string $view = 'filament-employee-management::filament.clusters.human-resources.resources.employee-resource.pages.view-employee-custom';

    public function getHeading(): string|Htmlable
    {
        return $this->record->full_name ?? 'Zaposlenik';
    }

    public function getBreadcrumb(): ?string
    {
        return "Zaposlenik";
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        if(auth()->user()->isEmployee() && auth()->user()->employee->id != $this->record->id){
            abort(403, "Nemate ovlasti za pregled ovog zaposlenika.");
        }

        $this->record->employee_details = [
            'Ime' => $this->record->first_name ?? "-",
            'Prezime' => $this->record->last_name ?? "-",
            "OIB" => $this->record->oib ?? "-",
            'Email' => $this->record->email ?? "-",
            "Broj telefona" => $this->record->phone_number ?? "-",
            'Adresa' => $this->record->address ?? "-",
            'Grad' => $this->record->city ?? "-",
            'Tarifa mobilnog broja' => $this->record->mobile_tariff ?? "-",
            "Napomena" => $this->record->note ?? "-",
        ];
    }

    public function employeeInfoList(Schema $schema): Schema
    {
        return EmployeeInfolist::configure($schema)->record($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
           EmployeeAction::editEmployee($this->record),
        ];
    }
}
