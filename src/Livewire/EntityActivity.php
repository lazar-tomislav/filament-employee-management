<?php

namespace Amicus\FilamentEmployeeManagement\Livewire;

use Amicus\FilamentEmployeeManagement\Models\Activity;
use Amicus\FilamentEmployeeManagement\Models\Task;
use Amicus\FilamentEmployeeManagement\Plugins\MentionRichContentPlugin;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\RichEditor;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Livewire\Component;

class EntityActivity extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?Model $entity = null;

    public string $entityType = '';

    public ?array $commentFormData = [];

    public ?int $editingActivityId = null;

    public bool $activitiesLoaded = false;

    public function mount(Model $entity, string $entityType): void
    {
        $this->entity = $entity;
        $this->entityType = $entityType;
        $this->commentForm->fill();
        $this->activitiesLoaded = true; // Automatski učitaj aktivnosti pri mount
    }

    public function loadActivities(): void
    {
        $this->activitiesLoaded = true;
    }

    public function commentForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                RichEditor::make('body')
                    ->label('Dodaj komentar')
                    ->autofocus()
                    ->placeholder('Napišite komentar...')
                    ->plugins([
                        MentionRichContentPlugin::make(),
                    ])
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'link',
                        'bulletList',
                        'orderedList',
                    ]),
            ])
            ->statePath('commentFormData');
    }

    #[On('entity-selected')]
    public function updateEntity(?int $entityId): void
    {
        if ($entityId) {
            $modelClass = $this->getModelClass();
            $this->entity = $modelClass::find($entityId);
            $this->commentForm->fill();
            $this->activitiesLoaded = false;
        }
    }

    private function getModelClass(): string
    {
        return match ($this->entityType) {
            'offer' => Offer::class,
            'task' => Task::class,
            default => throw new \InvalidArgumentException("Nepoznat tip entiteta: {$this->entityType}")
        };
    }

    public function addCommentAction(): Action
    {
        return Action::make('addComment')
            ->label(fn () => $this->editingActivityId ? 'Spremi izmjene' : 'Dodaj komentar')
            ->action(function () {
                try {
                    $data = $this->commentForm->getState();
                    if (empty($data['body']) || trim($data['body']) === '<p></p>') {
                        Notification::make()
                            ->title('Komentar ne može biti prazan')
                            ->warning()
                            ->duration(2000)
                            ->send();

                        return;
                    }

                    if ($this->editingActivityId) {
                        $activity = Activity::find($this->editingActivityId);
                        if ($activity && $activity->activityable_id === $this->entity->id && $activity->activityable_type === get_class($this->entity)) {
                            $activity->update(['body' => $data['body']]);
                        }
                        $this->editingActivityId = null;
                    } else {
                        Activity::create([
                            'activityable_type' => get_class($this->entity),
                            'activityable_id' => $this->entity->id,
                            'employee_id' => auth()->user()->employee->id,
                            'body' => $data['body'],
                        ]);
                    }

                    $this->commentForm->fill();
                } catch (\Exception $e) {
                    logger($e);
                    report($e);
                    Notification::make()
                        ->title('Greška prilikom dodavanja komentara')
                        ->danger()
                        ->send();
                }
            });
    }

    public function getActivitiesProperty()
    {
        if (! $this->entity || ! $this->activitiesLoaded) {
            return collect();
        }

        return $this->entity->activities()->with(['author', 'mentions'])->get();
    }

    public function editAction(): Action
    {
        return Action::make('edit')
            ->icon('heroicon-o-pencil')
            ->action(function (array $arguments) {
                $activity = Activity::find($arguments['activityId']);
                if ($activity && $activity->activityable_id === $this->entity->id && $activity->activityable_type === get_class($this->entity)) {
                    $this->editingActivityId = $activity->id;
                    $this->commentForm->fill(['body' => $activity->body]);
                    $this->dispatch('scroll-to-top');
                }
            });
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                $activity = Activity::find($arguments['activityId']);
                if ($activity && $activity->activityable_id === $this->entity->id && $activity->activityable_type === get_class($this->entity)) {
                    $activity->delete();
                    Notification::make()
                        ->title('Komentar je obrisan')
                        ->success()
                        ->send();
                }
            });
    }

    public function render()
    {
        return view('filament-employee-management::livewire.entity-activity', [
            'activities' => $this->activities,
        ]);
    }
}
