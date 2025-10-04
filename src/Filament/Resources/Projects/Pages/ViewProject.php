<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Actions\ProjectAction;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\ProjectResource;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Schemas\ProjectInfolist;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ViewProject extends ViewRecord implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string $resource = ProjectResource::class;

    protected string $view = 'filament-employee-management::filament.resources.projects.pages.view-project';

    public function getTitle(): string|Htmlable
    {
        return "Projekt: {$this->record->name}";
    }

    public function projectInfoList(Schema $schema): Schema
    {
        return ProjectInfolist::configure($schema)
            ->columns(2)
            ->record($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->slideOver()
                ->modalHeading("Uredi projekt"),

            ActionGroup::make([
                ProjectAction::generateIzjavaProjektant(),
                ProjectAction::generateZapisnikOPrimopredaji(),
                ProjectAction::generateImenovanjeVoditeljGradilista(),
            ])
                ->button()
                ->color("primary")
                ->icon(Heroicon::OutlinedDocumentText)
                ->label("Dokumentacija")

        ];
    }
}
