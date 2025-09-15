<?php

namespace Amicus\FilamentEmployeeManagement\Livewire\Projects;

use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Actions\ProjectAction;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Tables\ProjectsTable;
use Amicus\FilamentEmployeeManagement\Models\Project;
use App\Models\Client;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ProjectTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public StatusProjekta $status;

    public bool $isCollapsed = false;

    public ?Client $client = null;

    protected $listeners = [
        'project-created' => '$refresh',
    ];

    public function mount(StatusProjekta $status, null|string|int $clientId = null): void
    {
        $this->status = $status;
        $this->client = ($clientId !== null ? Client::find($clientId) : null);

        $sessionKey = 'project_table_collapsed_' . $this->status->value . ($this->client ? '_client_' . $this->client->id : '');

        if (session()->has($sessionKey)) {
            $this->isCollapsed = session()->get($sessionKey);
        } else {
            $projectCount = Project::query()
                ->where('status', $this->status)
                ->when($this->client, fn($query) => $query->where('client_id', $this->client->id))
                ->count();

            $this->isCollapsed = ($projectCount === 0);
        }
    }

    public function toggleCollapse(): void
    {
        $this->isCollapsed = !$this->isCollapsed;

        $sessionKey = 'project_table_collapsed_' . $this->status->value . ($this->client ? '_client_' . $this->client->id : '');
        session()->put($sessionKey, $this->isCollapsed);
    }

    public function quickCreateAction(): Action
    {
        return ProjectAction::quickCreateProject($this->status, $this->client?->id)->after(fn()=>$this->dispatch('project-created'));
    }

    public function table(Table $table): Table
    {

        return ProjectsTable::configure($table)
            ->paginated(false)
            ->query(
                Project::query()
                    ->where('status', $this->status)
                    ->when($this->client, fn($query) => $query->where('client_id', $this->client->id))
            );
    }

    public function render(): View
    {
        return view('filament-employee-management::livewire.projects.project-table');
    }
}
