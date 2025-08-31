<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeLog extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'project_id',
        'date',
        'hours',
        'description',
        'status',
        'log_type',
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Assuming a Project model exists in the main app
    // If not, this can be adjusted or made polymorphic
    public function project()
    {
        // This assumes a Project model exists in App\Models
        // This might need to be configurable in the package config
        return $this->belongsTo(\App\Models\Project::class);
    }
}
