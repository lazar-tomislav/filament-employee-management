<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskUpdateMention extends Model
{
    protected $fillable = [
        'task_update_id',
        'mentioned_employee_id',
    ];

    public function taskUpdate(): BelongsTo
    {
        return $this->belongsTo(TaskUpdate::class);
    }

    public function mentionedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'mentioned_employee_id');
    }
}
