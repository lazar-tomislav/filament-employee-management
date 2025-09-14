<?php

namespace Amicus\FilamentEmployeeManagement\Livewire\Tasks;

use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Actions\TaskActivityAction;
use Amicus\FilamentEmployeeManagement\Models\Task;
use Amicus\FilamentEmployeeManagement\Models\TaskUpdate;
use Amicus\FilamentEmployeeManagement\Plugins\MentionRichContentPlugin;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\RichEditor;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskActivity extends Component implements HasSchemas, HasActions
{
    use InteractsWithSchemas;
    use InteractsWithActions;

    public ?Task $task = null;

    public ?array $commentFormData = [];

    public ?int $editingUpdateId = null;

    public bool $updatesLoaded = false;

    public function mount(Task $task): void
    {
        $this->task = $task;
        $this->commentForm->fill();
    }

    public function loadUpdates(): void
    {
        $this->updatesLoaded = true;
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

    #[On('task-selected')]
    public function updateTask(?int $taskId): void
    {
        if ($taskId) {
            $this->task = Task::find($taskId);
            $this->commentForm->fill();
            $this->updatesLoaded = false; // Reset loading state
        }
    }

    public function addCommentAction(): Action
    {
        return Action::make('addComment')
            ->label(fn()=> $this->editingUpdateId ? "Spremi izmjene" : 'Dodaj komentar')
            ->action(function () {
                try {
                    $data = $this->commentForm->getState();
                    if(empty($data['body']) || trim($data['body']) === '<p></p>') {
                        Notification::make()->title('Komentar ne može biti prazan')->warning()->duration(2000)->send();
                        return;
                    }

                    if ($this->editingUpdateId) {
                        // Update existing comment
                        $update = TaskUpdate::find($this->editingUpdateId);
                        if ($update && $update->task_id === $this->task->id) {
                            $update->update(['body' => $data['body']]);
                        }
                        $this->editingUpdateId = null;
                    } else {
                        // Create new comment
                        TaskUpdate::query()->create([
                            'task_id' => $this->task->id,
                            'employee_id' => auth()->user()->employee->id,
                            'body' => $data['body'],
                        ]);
                    }

                    $this->commentForm->fill(); // Clear the form
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

    public function getUpdatesProperty()
    {
        if (!$this->task || !$this->updatesLoaded) {
            return collect();
        }

        return $this->task->updates()->with(['author', 'mentions'])->get();
    }

    public function editAction(): Action
    {
        return Action::make('edit')
            ->icon('heroicon-o-pencil')
            ->action(function (array $arguments) {
                $update = TaskUpdate::find($arguments['updateId']);
                if ($update && $update->task_id === $this->task->id) {
                    $this->editingUpdateId = $update->id;
                    $this->commentForm->fill(['body' => $update->body]);
                    $this->dispatch('scroll-to-top');
                }
            });
    }

    public function deleteAction(): Action
    {
        return TaskActivityAction::deleteAction($this->task);
    }

    public function render()
    {
        return view('filament-employee-management::livewire.tasks.task-activity', [
            'updates' => $this->updates,
        ]);
    }
}
