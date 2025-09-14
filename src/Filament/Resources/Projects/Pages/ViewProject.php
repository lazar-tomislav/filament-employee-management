<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected string $view= 'filament.resources.projects.pages.view-project';

    public function getTitle(): string|Htmlable
    {
        return "Projekt: {$this->record->name}";
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
            ->slideOver()
            ->modalHeading("Uredi projekt"),
        ];
    }
}
