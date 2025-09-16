<?php

namespace Amicus\FilamentEmployeeManagement\Livewire\Tasks;

use Amicus\FilamentEmployeeManagement\Enums\TaskStatus;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Actions\TaskAction;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Tables\TasksTable;
use Amicus\FilamentEmployeeManagement\Models\Project;
use Amicus\FilamentEmployeeManagement\Models\Task;
use App\Models\Client;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public TaskStatus $status;

    public bool $isCollapsed = false;

    public ?Client $client = null;

    public ?Project $project = null;

    public ?string $searchQuery = null;

    public ?int $assigneeId = null;

    protected $listeners = [
        'task-created' => '$refresh',
    ];

    public function mount(TaskStatus $status, null|string|int $clientId = null, null|string|int $projectId = null): void
    {
        $this->status = $status;
        $this->project = ($projectId !== null ? Project::find($projectId) : null);
        $this->client = ($clientId !== null ? Client::find($clientId) : null);
        if(! $this->client && $this->project) {
            $this->client = $this->project->client;
        }

        $sessionKey = 'task_table_collapsed_' . $this->status->value
            . ($this->client ? '_client_' . $this->client->id : '')
            . ($this->project ? '_project_' . $this->project->id : '');

        if(session()->has($sessionKey)){
            $this->isCollapsed = session()->get($sessionKey);
        }else{
            $taskCount = Task::query()
                ->where('status', $this->status)
                ->when($this->client, fn($query) => $query->where('client_id', $this->client->id))
                ->when($this->project, fn($query) => $query->where('project_id', $this->project->id))
                ->count();

            $this->isCollapsed = ($taskCount === 0);
        }
    }

    public function toggleCollapse(): void
    {
        $this->isCollapsed = !$this->isCollapsed;

        $sessionKey = 'task_table_collapsed_' . $this->status->value
            . ($this->client ? '_client_' . $this->client->id : '')
            . ($this->project ? '_project_' . $this->project->id : '');
        session()->put($sessionKey, $this->isCollapsed);
    }

    public function quickCreateAction(): Action
    {
        return TaskAction::quickCreateTask($this, $this->status, $this->client?->id, $this->project?->id);
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
                    ->where('status', $this->status)
                    ->when($this->client, fn($query) => $query->where('client_id', $this->client->id))
                    ->when($this->project, fn($query) => $query->where('project_id', $this->project->id))
                    ->when($this->searchQuery, fn($query) => $query->where('title', 'like', '%' . $this->searchQuery . '%'))
                    ->when($this->assigneeId, fn($query) => $query->where('assignee_id', $this->assigneeId))
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

    public function openConversation(string $recordId):void{
        $this->dispatch('open-modal', id: 'edit-task-modal', params: [
            'taskId' => $recordId,
        ]);
    }

    public function render(): View
    {
        return view('filament-employee-management::livewire.tasks.task-table');
    }
}
