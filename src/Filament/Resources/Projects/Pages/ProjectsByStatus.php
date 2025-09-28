<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages;

use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ProjectsByStatus extends ListRecords
{
    protected string $view = 'filament-employee-management::filament.resources.projects.pages.list-projects';

    protected static string $resource = ProjectResource::class;
    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $breadcrumb = "Popis";

    protected static ?string $title = null;

    public ?StatusProjekta $statusProjekta = null;

    public function mount(): void
    {
        $slug = request()->segment(3);
        $status = \Amicus\FilamentEmployeeManagement\Enums\StatusProjekta::fromSlug($slug);
        if(!$status){
            abort(404, "Status projekta nije pronađen.");
        }
        $this->statusProjekta = $status;

    }

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
