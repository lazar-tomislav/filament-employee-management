<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Actions\ProjectAction;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ProjectsByStatus extends ListRecords
{
    protected string $view = 'filament-employee-management::filament.resources.projects.pages.list-projects';

    protected static string $resource = ProjectResource::class;
    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $breadcrumb = "Popis";

    public function getTitle(): string|Htmlable
    {
        return "Projekti";
    }

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
