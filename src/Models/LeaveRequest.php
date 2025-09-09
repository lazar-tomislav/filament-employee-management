<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestType;
use Amicus\FilamentEmployeeManagement\Observers\LeaveRequestObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([LeaveRequestObserver::class])]
class LeaveRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'leave_allowance_id',
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
        'type' => LeaveRequestType::class,
        'status' => LeaveRequestStatus::class,
    ];


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function leaveAllowance()
    {
        return $this->belongsTo(LeaveAllowance::class);
    }

    protected function absence(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->start_date->format('d.m.Y') . ' - ' . $this->end_date->format('d.m.Y'),
        );
    }

    public static function getLeaveRequestsForDate(int $employeeId, string $date)
    {
        return self::query()
            ->where('employee_id', $employeeId)
            ->where('status', LeaveRequestStatus::APPROVED)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->get();
    }
}
