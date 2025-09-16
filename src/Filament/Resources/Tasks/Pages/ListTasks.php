<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Actions\TaskAction;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\TaskResource;
use App\Filament\Forms\Components\EmployeeAssigneeField;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected string $view = 'filament-employee-management::filament.resources.tasks.pages.list-tasks';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->schema([
                TextInput::make('title')
                    ->hiddenLabel()
                    ->label('Naslov zadatka')
                    ->placeholder('Pretraži po naslovu...')
                    ->autofocus()
                    ->extraInputAttributes(['class' => "w-84"])
                    ->live(debounce: 300)
                    ->afterStateUpdated(function ($state) {
                        $this->dispatch('task-search', query: $state);
                    }),
                EmployeeAssigneeField::make('assignee_id')
                    ->hiddenLabel()
                    ->live(debounce: 300)
                    ->afterStateUpdated(function ($state) {
                        $this->dispatch('task-assignee-filter', assigneeId: $state);
                    }),
            ])
            ->statePath('data');
    }

    public function getTitle(): string|Htmlable
    {
        return "Zadaci";
    }

    public function taskCreateAction(): Action
    {
        return TaskAction::createAction()
            ->icon(Heroicon::OutlinedBolt)
            ->after(fn() => $this->dispatch('task-created'));
    }
}
