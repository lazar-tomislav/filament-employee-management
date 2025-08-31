<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Illuminate-|-Database-|-Eloquent-|-Factories-|-HasFactory;
use Illuminate-|-Database-|-Eloquent-|-Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'status',
        'start_date',
        'end_date',
        'days_count',
        'notes',
        'rejection_reason',
        'approved_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_count' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}
