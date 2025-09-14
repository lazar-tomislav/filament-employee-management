<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Actions;

use Amicus\FilamentEmployeeManagement\Enums\TaskStatus;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Schemas\TaskForm;
use Amicus\FilamentEmployeeManagement\Livewire\Tasks\TaskTable;
use Amicus\FilamentEmployeeManagement\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TaskAction
{

    public static function createAction(): CreateAction
    {
        return CreateAction::make()
            ->slideOver()
            ->schema(fn($schema) => TaskForm::configure($schema))
            ->label("Kreiraj zadatak")
            ->modalHeading("Kreiraj zadatak")
            ->successNotificationTitle("Zadatak uspješno kreiran.")
            ->action(function (array $data) {
                try{
                    Task::query()->create($data);
                }catch(\Exception $e){
                    report($e);
                    Notification::make()->title('Greška')->body('Neuspješno kreiranje zadatka.')->danger()->send();
                }
            });

    }

    public static function quickCreateTask(TaskTable $component, TaskStatus $status, ?int $clientId, ?int $projectId): Action
    {
        return Action::make('quick_create')
            ->icon(Heroicon::OutlinedPlus)
            ->hiddenLabel()
            ->modalHeading("Novi zadatak")
            ->slideOver()
            ->fillForm(fn() => [
                "assignee_id" => auth()->user()->employee?->id,
            ])
            ->schema(fn($schema) => TaskForm::configure($schema, isQuickProjectCreate:true))
            ->action(function ($data) use ($status, $component, $clientId, $projectId) {
                try{
                    $data['client_id'] = $clientId;
                    $data['project_id'] = $projectId;
                    $data['status'] = $status->value;
                    $data['creator_id'] = auth()->id();

                    $taskId = Task::query()->insertGetId($data);
                    Notification::make()->title('Zadatak uspješno kreiran')->success()->send();

                    $component->dispatch('task-created');
                    $component->dispatch('open-modal', id: 'edit-task-modal', params: [
                        'taskId' => $taskId,
                    ]);
                }catch(\Exception $e){
                    report($e);
                    Notification::make()->title('Greška prilikom kreiranja zadatka')->danger()->send();
                }
            });
    }

    public static function editInCustomModal(Table $table): Action
    {
        return Action::make("edit")
            ->label("Uredi")
            ->icon(Heroicon::OutlinedPencil)->action(function ($record) use ($table) {
                $table->getLivewire()->dispatch('open-modal', id: 'edit-task-modal', params: [
                    'taskId' => $record->id,
                ]);
            });
    }
}
