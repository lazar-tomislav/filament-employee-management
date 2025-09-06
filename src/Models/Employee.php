<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestType;
use Amicus\FilamentEmployeeManagement\Observers\EmployeeObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

#[ObservedBy([EmployeeObserver::class])]
class Employee extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'city',
        'oib',
        'mobile_tariff',
        'note',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_active', true);
        });
    }

    public function leaveAllowances()
    {
        return $this->hasMany(LeaveAllowance::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    protected function fullName():Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }
    public static function options()
    {
        // pluck all to array (first_name, last name (email) as value) and id as key
        return self::all()->pluck(function ($employee) {
            return "{$employee->first_name} {$employee->last_name} ({$employee->email})";
        }, 'id');
    }

    public function getWorkhoursForMonth(Carbon $month): array
    {
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        // Get all holidays for the month
        $holidays = Holiday::getHolidaysForMonth($month);

        // Calculate available work hours
        $availableHours = 0;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = $startDate->copy()->addDays($day - 1);
            if ($currentDate->isWeekday() && !in_array($currentDate->format('Y-m-d'), $holidays)) {
                $availableHours += 8;
            }
        }

        // Calculate worked hours from TimeLog
        $workedHours = TimeLog::where('employee_id', $this->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('hours');

        // Calculate vacation and sick leave hours
        $vacationHours = 0;
        $sickLeaveHours = 0;

        $leaveRequests = LeaveRequest::where('employee_id', $this->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->get();

        foreach ($leaveRequests as $leaveRequest) {
            $leaveStartDate = Carbon::parse($leaveRequest->start_date);
            $leaveEndDate = Carbon::parse($leaveRequest->end_date);

            for ($date = $leaveStartDate->copy(); $date->lte($leaveEndDate); $date->addDay()) {
                if ($date->between($startDate, $endDate) && $date->isWeekday() && !in_array($date->format('Y-m-d'), $holidays)) {
                    if (in_array($leaveRequest->type, [LeaveRequestType::ANNUAL_LEAVE->value, LeaveRequestType::PAID_LEAVE->value])) {
                        $vacationHours += 8;
                    } elseif ($leaveRequest->type === LeaveRequestType::SICK_LEAVE->value) {
                        $sickLeaveHours += 8;
                    }
                }
            }
        }

        return [
            'worked_hours' => (float) $workedHours,
            'available_hours' => $availableHours,
            'vacation_hours' => $vacationHours,
            'sick_leave_hours' => $sickLeaveHours,
        ];
    }
}
