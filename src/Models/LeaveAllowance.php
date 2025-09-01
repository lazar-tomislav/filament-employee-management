<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Observers\LeaveAllowanceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([\Amicus\FilamentEmployeeManagement\Observers\LeaveAllowanceObserver::class])]
class LeaveAllowance extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'year',
        'total_days',
        'carried_over_days',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'total_days' => 'integer',
        'carried_over_days' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
