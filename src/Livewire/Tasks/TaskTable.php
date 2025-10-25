<?php

namespace Amicus\FilamentEmployeeManagement\Livewire\Tasks;

use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
use Amicus\FilamentEmployeeManagement\Enums\TaskStatus;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Actions\TaskAction;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Tables\TasksTable;
use Amicus\FilamentEmployeeManagement\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public TaskStatus|StatusProjekta $status;

    public bool $isCollapsed = false;

    public bool $isProjectStatusMode = false;

    public ?string $searchQuery = null;

    public ?int $assigneeId = null;

    protected $listeners = [
        'task-created' => '$refresh',
        'status-updated' => '$refresh',
    ];

    public function mount(TaskStatus|StatusProjekta $status): void
    {
        $this->status = $status;
        $this->isProjectStatusMode = $status instanceof StatusProjekta;

        $sessionKey = 'task_table_collapsed_' . $this->status->value;

        if(session()->has($sessionKey)){
            $this->isCollapsed = session()->get($sessionKey);
        }else{
            $taskCount = Task::query()
                ->when($this->isProjectStatusMode,
                    fn($query) => $query->where('project_status', $this->status)->whereNotNull('project_id'),
                    fn($query) => $query->where('status', $this->status)
                )
                ->count();

            $this->isCollapsed = ($taskCount === 0);
        }
    }

    public function toggleCollapse(): void
    {
        $this->isCollapsed = !$this->isCollapsed;

        $sessionKey = 'task_table_collapsed_' . $this->status->value;
        session()->put($sessionKey, $this->isCollapsed);
    }

    public function quickCreateAction(): Action
    {
        return TaskAction::quickCreateTask($this, $this->status);
    }

    public function table(Table $table): Table
    {
        return TasksTable::configure($table)
            ->paginated(false)
//            ->filters([
//                Filters\Filter::make('is_onetime')
//                    ->toggle()
//                    ->label('Samo jednokratni zadaci')
//                    ->query(fn($query) => $query->whereNull('project_id'))
//            ])
            ->deferFilters(false)
            ->query(
                Task::query()
                    ->when($this->isProjectStatusMode,
                        fn($query) => $query->where('project_status', $this->status)->whereNotNull('project_id'),
                        fn($query) => $query->where('status', $this->status)
                    )
                    ->when($this->searchQuery, fn($query) => $query->where('title', 'like', '%' . $this->searchQuery . '%'))
                    ->when($this->assigneeId, fn($query) => $query->where('assignee_id', $this->assigneeId))
                    ->with(['assignee', 'project'])
            );
    }

    #[On('filter-tasks')]
    public function filterTasks(array $filters = []): void
    {
        if (isset($filters['query'])) {
            logger("Filtering tasks with query: {$filters['query']}");
            $this->searchQuery = $filters['query'];
        }else{
            $this->searchQuery = null;
        }

        if (isset($filters['assigneeId'])) {
            logger("Filtering tasks by assignee: {$filters['assigneeId']}");
            $this->assigneeId = $filters['assigneeId'];
        }else{
            $this->assigneeId = null;
        }

        $this->resetTable();
    }

    public function openConversation(string $recordId): void
    {
        $this->dispatch('open-modal', id: 'edit-entity-modal', params: [
            'entityId' => $recordId,
        ]);
    }

    public function render(): View
    {
        return view('filament-employee-management::livewire.tasks.task-table');
    }
}
