<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Enums\TaskStatus;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\TaskResource;
use Amicus\FilamentEmployeeManagement\Observers\TaskObserver;
use App\Models\Client;
use App\Traits\HasActivities;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(TaskObserver::class)]
class Task extends Model
{
    use HasFactory, SoftDeletes, HasActivities;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'project_status',
        'client_id',
        'project_id',
        'creator_id',
        'assignee_id',
        'due_at',
        'is_billable',
        'billed_amount',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => TaskStatus::class,
        'project_status' => \Amicus\FilamentEmployeeManagement\Enums\StatusProjekta::class,
        'due_at' => 'datetime',
        'is_billable' => 'boolean',
    ];

    /**
     * Checks if the task is a stand-alone (one-off) task, not tied to a project.
     */
    public function isStandAlone(): bool
    {
        return is_null($this->project_id);
    }

    /**
     * Get the client that owns the task.
     */
    public function client(): BelongsTo
    {
        // Assumes an App\Models\Client model exists
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the project that the task belongs to (if any).
     */
    public function project(): BelongsTo
    {
        // Assumes an App\Models\Project model exists
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who created the task.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'creator_id');
    }

    /**
     * Get the user who is assigned to complete the task.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assignee_id');
    }

    public function viewUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => TaskResource::getUrl('index')."?modalEntityId={$this->id}",
        );

    }
}
