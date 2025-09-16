<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Actions\TaskAction;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\TaskResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected string $view = 'filament-employee-management::filament.resources.tasks.pages.list-tasks';

    public function getTitle(): string|Htmlable
    {
        return "Zadaci";
    }

    protected function getHeaderActions(): array
    {
        return [
            TaskAction::createAction()
                ->icon(Heroicon::OutlinedBolt)
                ->after(fn() => $this->dispatch('task-created')),
        ];
    }
}
