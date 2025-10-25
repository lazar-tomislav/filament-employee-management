<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'employee_id',
        'description',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id')->withTrashed();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(\Amicus\FilamentEmployeeManagement\Models\Task::class, 'project_id');
    }

    public static function options()
    {
        return self::all()->pluck(function ($employee) {
            return $employee->name;
        }, 'id');
    }
}
