<?php

namespace Amicus\FilamentEmployeeManagement\Livewire;

use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Schemas\TaskForm;
use Amicus\FilamentEmployeeManagement\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class EditEntityModal extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?Model $entity = null;

    public ?array $entityFormData = [];

    public string $activeTab = 'updates';

    public string $entityType = '';

    public string $entityId = '';

    #[Url]
    public ?string $modalEntityId = '';

    public function mount(string $entityType = 'task'): void
    {
        if ($this->modalEntityId) {
            $this->loadEntity($this->modalEntityId);
        }
        $this->entityType = $entityType;
        $this->entityForm->fill();

    }

    private function loadEntity(string $entityId): void
    {
        $modelClass = $this->getModelClass();
        $this->entity = $modelClass::find($entityId);

        if ($this->entity) {
            $this->entityForm->fill($this->entity->attributesToArray());
            $this->entityId = $this->entity->id;
        } else {
            Notification::make('no_entity_found')
                ->title('Entitet koji pokušavate otvoriti preko linka ne postoji.')
                ->warning()
                ->send();
        }
    }

    private function getModelClass(): string
    {
        return match ($this->entityType) {
            'task' => Task::class,
            default => throw new \InvalidArgumentException("Nepoznat tip entiteta: {$this->entityType}")
        };
    }

    public function entityForm(Schema $schema): Schema
    {
        $schema = match ($this->entityType) {
            'task' => TaskForm::configure($schema),
            default => []
        };

        return $schema
            ->statePath('entityFormData');
    }

    #[On('open-modal')]
    public function openModal(array $params = []): void
    {
        if (isset($params['entityId'])) {
            $this->loadEntity($params['entityId']);
        }
    }

    #[On('close-modal')]
    public function closeModalEvt(): void
    {
        $this->modalEntityId = null;
        $this->entityId = '';
    }

    public function onModalLoad(): void
    {
        if ($this->entityId && $this->entity !== null) {
            $this->dispatch('open-modal', id: 'edit-entity-modal', params: [
                'entityId' => $this->entityId,
            ]);
        }
    }

    public function closeModal(): void
    {
        $this->modalEntityId = null;
        $this->entityId = '';
        $this->dispatch('close-modal', id: 'edit-entity-modal');
    }

    public function saveAction(): Action
    {
        return Action::make('save')
            ->label('Spremi')
            ->action(function () {
                try {
                    $this->entity->update($this->entityForm->getState());

                    $entityLabel = match ($this->entityType) {
                        default => 'Entitet'
                    };

                    Notification::make()
                        ->title("{$entityLabel} je uspješno ažuriran")
                        ->success()
                        ->send();

                    $this->closeModal();
                    $this->dispatch('entity-updated');
                } catch (\Exception $e) {
                    report($e);
                    Notification::make()
                        ->title('Greška prilikom ažuriranja')
                        ->danger()
                        ->send();
                }
            });
    }

    public function cancelAction(): Action
    {
        return Action::make('cancel')
            ->label('Odustani')
            ->color('gray')
            ->action(fn () => $this->closeModal());
    }

    public function render()
    {
        return view('filament-employee-management::livewire.edit-entity-modal');
    }
}
