<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Observers\MonthlyWorkReportObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([MonthlyWorkReportObserver::class])]
class MonthlyWorkReport extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'for_month',
        'approved_at',
        'denied_at',
        'deny_reason',
        'total_available_hours',
        'work_hours',
        'overtime_hours',
        'vacation_hours',
        'sick_leave_hours',
        'other_hours',
    ];

    protected $casts = [
        'for_month' => 'date',
        'approved_at' => 'datetime',
        'denied_at' => 'datetime',
        'total_available_hours' => 'decimal:2',
        'work_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'vacation_hours' => 'decimal:2',
        'sick_leave_hours' => 'decimal:2',
        'other_hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
