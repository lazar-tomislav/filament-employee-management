<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Actions;

use Amicus\FilamentEmployeeManagement\Enums\TaskStatus;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Schemas\TaskForm;
use Amicus\FilamentEmployeeManagement\FilamentEmployeeManagementServiceProvider;
use Amicus\FilamentEmployeeManagement\Livewire\Tasks\TaskTable;
use Amicus\FilamentEmployeeManagement\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class TaskAction
{
    public static function createAction(): Action
    {
        return Action::make("delete")
            ->slideOver()
            ->schema(function ($schema) {
                return TaskForm::configure($schema);
            })
            ->label("Kreiraj zadatak")
            ->modalHeading("Kreiraj zadatak")
            ->successNotificationTitle("Zadatak uspješno kreiran.")
            ->action(function (array $data) {
                try{
                    if (!auth()->user()->employee) {
                        Notification::make()->title('Greška')->body('Korisnik nema povezan Employee zapis.')->danger()->send();
                        return;
                    }
                    Task::query()->create($data);
                }catch(\Exception $e){
                    report($e);
                    Notification::make()->title('Greška')->body('Neuspješno kreiranje zadatka.')->danger()->send();
                }
            });

    }

    public static function quickCreateTask(TaskTable $component, TaskStatus $status): Action
    {
        return Action::make('quick_create')
            ->icon(Heroicon::OutlinedPlus)
            ->hiddenLabel()
            ->modalHeading("Novi zadatak")
            ->slideOver()
            ->fillForm(fn() => [
                "assignee_id" => auth()->user()->employee?->id,
            ])
            ->schema(function ($schema) {
                return TaskForm::configure($schema);
            })
            ->action(function ($data) use ($status, $component) {
                try{
                    if (!auth()->user()->employee) {
                        Notification::make()->title('Greška')->body('Korisnik nema povezan Employee zapis.')->danger()->send();
                        return;
                    }

                    $data['status'] = $status->value;
                    $data['creator_id'] = auth()->user()->employee->id;

                    $task = Task::query()->create($data);
                    $taskId = $task->id;
                    Notification::make()->title('Zadatak uspješno kreiran')->success()->send();

                    $component->dispatch('task-created');
                    $component->dispatch('open-modal', id: 'edit-entity-modal', params: [
                        'entityId' => $taskId,
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
                $table->getLivewire()->dispatch('open-modal', id: 'edit-entity-modal', params: [
                    'entityId' => $record->id,
                ]);
            });
    }

    public static function changeStatusAction(Table $table): Action
    {
        return Action::make('changeStatus')
            ->label('Promijeni status')
            ->icon('heroicon-o-arrow-path')
            ->schema(fn($record) => [
                Select::make('status')
                    ->label('Novi status')
                    ->autofocus()
                    ->native(false)
                    ->options(fn($record)=>
                        collect(TaskStatus::cases())
                            ->filter(fn($case) => $case !== $record->status)
                            ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
                    )
                    ->required()
            ])
            ->action(function (array $data, $record, $component) use ($table): void {
                try{
                    $newStatus = TaskStatus::from($data['status']);
                    $record->update(['status' => $newStatus]);
                    $table->getLivewire()->dispatch('task-created');
                }catch(\Exception $e){
                    report($e);
                    Notification::make()->title('Greška prilikom promjene statusa')->danger()->send();
                }
            });
    }
}
