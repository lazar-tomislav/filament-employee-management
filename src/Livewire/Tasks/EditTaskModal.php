<?php

namespace Amicus\FilamentEmployeeManagement\Livewire\Tasks;

use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Schemas\TaskForm;
use Amicus\FilamentEmployeeManagement\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class EditTaskModal extends Component implements HasSchemas, HasActions
{
    use InteractsWithSchemas;
    use InteractsWithActions;

    public ?Task $task = null;

    public ?array $taskFormData = [];

    public string $activeTab = 'updates'; // updates

    #[Url]
    public ?string $taskId = '';

    public function mount(): void
    {
        $this->editTaskForm->fill();
        if($this->taskId){
            $this->task = Task::find($this->taskId);
            if($this->task){
                // Reset form state and fill with task data
                $this->editTaskForm->fill($this->task->attributesToArray());
            }else{
                Notification::make("no_task_id_found")->title('Zadatak koji pokušavate otvoriti preko linka ne postoji.')->warning()->send();
            }
        }
    }

    public function editTaskForm(Schema $schema): Schema
    {
        return TaskForm::configure($schema)
            ->statePath('taskFormData');
    }

    #[On("open-modal")]
    public function openModal(array $params = []): void
    {
        if(isset($params['taskId'])){
            $this->task = Task::find($params['taskId']);
            if($this->task){
                // Reset form state and fill with task data
                $this->editTaskForm->fill($this->task->attributesToArray());
                $this->taskId = $this->task->id;
            }
        }
    }

    #[On("close-modal")]
    public function closeModalEvt(): void
    {
        $this->taskId = null;
    }

    public function onModalLoad():void
    {
        if($this->taskId && $this->task != null){
            $this->dispatch('open-modal', id: 'edit-task-modal', params: [
                'taskId' => $this->taskId,
            ]);
        }
    }

    public function closeModal(): void
    {
        $this->taskId = null;
        $this->dispatch('close-modal', id: 'edit-task-modal');
    }

    public function saveAction(): Action
    {
        return Action::make('save')
            ->label('Spremi')
            ->action(function () {
                try{

                    $this->task->update($this->editTaskForm->getState());
                    Notification::make()->title('Zadatak je uspješno ažuriran')->success()->send();
                    $this->closeModal();
                    $this->dispatch('task-created');
                }catch(\Exception $e){
                    report($e);
                    Notification::make()->title('Greška prilikom ažuriranja zadatka')->danger()->send();
                }
            });
    }

    public function cancelAction(): Action
    {
        return Action::make('cancel')
            ->label('Odustani')
            ->color('gray')
            ->action(fn() => $this->closeModal());
    }

    public function render()
    {
        return view('livewire.tasks.edit-task-modal');
    }
}
