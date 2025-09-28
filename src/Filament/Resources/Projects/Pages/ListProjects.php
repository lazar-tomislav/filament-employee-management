<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Actions\ProjectAction;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\ProjectResource;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Widgets\ProjectStatsWidget;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListProjects extends ListRecords
{
    protected string $view = 'filament-employee-management::filament.resources.projects.pages.list-projects';

    protected static string $resource = ProjectResource::class;

    public function getTitle(): string|Htmlable
    {
        return "Projekti";
    }

    protected static ?string $breadcrumb = "Popis";

    protected function getHeaderActions(): array
    {
        return [
            ProjectAction::createAction()->after(fn() => $this->dispatch('project-created')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProjectStatsWidget::class,
        ];
    }
}
