<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages;

use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\ProjectResource;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class FinancesPage extends Page
{
    protected string $view = 'filament-employee-management::filament.resources.projects.pages.finances-page';

    protected static string $resource = ProjectResource::class;
    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $breadcrumb = "Pregled";

    protected static ?string $title = null;

//    protected static string | BackedEnum | null $navigationIcon=Heroicon::OutlinedCurrencyEuro;
    protected static ?int $navigationSort = 100;
    protected static string|\UnitEnum|null $navigationGroup = "Projekti";
    protected static ?string $navigationLabel = "10. Financije";

    public function mount(): void
    {

    }

    public function getTitle(): string|Htmlable
    {
        return "Financije";
    }

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
