<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages;

use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\ActionGroup;
use Filament\Pages\Page;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class ProjectSchedule extends Page
{
    protected string $view = 'filament-employee-management::filament.resources.projects.pages.project-schedule';

    protected static string $resource = ProjectResource::class;
    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $breadcrumb = "Pregled";

    protected static ?int $navigationSort =60;
    protected static string | UnitEnum | null $navigationGroup="Projekti";
    protected static ?string $navigationLabel="6. Raspored";
    protected static ?string $title = null;

    public function mount(): void
    {

    }

    public function getTitle(): string|Htmlable
    {
        return "Raspored";
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                \Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Actions\ProjectAction::generateGradilisteLista(),
                \Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Actions\ProjectAction::generateRobaGradiliste(),
            ])->label("Prazne Šprance")->button()->color("primary"),
        ];
    }
}
