<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
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

    const HOURS_PER_WORK_DAY = 8;
    const WORK_DAYS = [
        Carbon::MONDAY,
        Carbon::TUESDAY,
        Carbon::WEDNESDAY,
        Carbon::THURSDAY,
        Carbon::FRIDAY,
    ];

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

    protected function telegramChatId():Attribute
    {
        return Attribute::make(
            get: fn () => config('employee-management.telegram-bot-api.general_notification'),
        );
    }

    public function leaveAllowances()
    {
        return $this->hasMany(LeaveAllowance::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function routeNotificationForTelegram()
    {
        return config('employee-management.telegram-bot-api.general_notification');
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

    private function getDailyWorkHours(Carbon $date): float
    {
        return (float) TimeLog::where('employee_id', $this->id)
            ->whereDate('date', $date->format('Y-m-d'))
            ->sum('hours');
    }

    private function getDailyLeaveHours(Carbon $date): array
    {
        $leaveHours = [
            'vacation_hours' => 0,
            'sick_leave_hours' => 0,
            'other_hours' => 0,
        ];

        if (!in_array($date->dayOfWeek, self::WORK_DAYS)) {
            return $leaveHours;
        }

        $leaveRequest = LeaveRequest::getLeaveRequestsForDate($this->id, $date)->first();

        if ($leaveRequest) {
            if ($leaveRequest->type === LeaveRequestType::ANNUAL_LEAVE) {
                $leaveHours['vacation_hours'] = self::HOURS_PER_WORK_DAY;
            } elseif ($leaveRequest->type === LeaveRequestType::SICK_LEAVE) {
                $leaveHours['sick_leave_hours'] = self::HOURS_PER_WORK_DAY;
            } elseif ($leaveRequest->type === LeaveRequestType::PAID_LEAVE) {
                $leaveHours['other_hours'] = self::HOURS_PER_WORK_DAY;
            }else{
                report(new \Exception("Unknown leave request type: {$leaveRequest->type}"));
            }
        }

        return $leaveHours;
    }

    public function getMonthlyWorkReport(Carbon $month): array
    {
        $report = [
            'daily_data' => [],
            'totals' => [
                'work_hours' => 0.0,
                'overtime_hours' => 0.0,
                'vacation_hours' => 0.0,
                'sick_leave_hours' => 0.0,
                'other_hours' => 0.0,
                'available_hours' => 0.0,
            ],
        ];

        $daysInMonth = $month->daysInMonth;
        $holidays = Holiday::getHolidaysForMonth($month);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $month->copy()->setDay($day);

            $totalDailyWorkHours = $this->getDailyWorkHours($date);
            $dailyWorkHours = 0.0;
            $dailyOvertimeHours = 0.0;

            $leaveHours = $this->getDailyLeaveHours($date);
            $dailyVacationHours = $leaveHours['vacation_hours'];
            $dailySickLeaveHours = $leaveHours['sick_leave_hours'];
            $dailyOtherHours = $leaveHours['other_hours'];

            $isWorkDayOfWeek = in_array($date->dayOfWeek, self::WORK_DAYS);
            $isPublicHoliday = in_array($date->format('Y-m-d'), $holidays);
            $isOnLeave = $dailyVacationHours > 0 || $dailySickLeaveHours > 0 || $dailyOtherHours > 0;

            $isStandardWorkDay = $isWorkDayOfWeek && !$isPublicHoliday && !$isOnLeave;

            // Available hours are calculated based on potential work days (ignoring personal leave)
            if ($isWorkDayOfWeek && !$isPublicHoliday) {
                $report['totals']['available_hours'] += self::HOURS_PER_WORK_DAY;
            }

            if ($isStandardWorkDay) {
                $dailyWorkHours = $totalDailyWorkHours;
                if ($totalDailyWorkHours > self::HOURS_PER_WORK_DAY) {
                    $dailyWorkHours = self::HOURS_PER_WORK_DAY;
                    $dailyOvertimeHours = $totalDailyWorkHours - self::HOURS_PER_WORK_DAY;
                }
            } else { // It's a weekend, a holiday, or a personal leave day
                $dailyOvertimeHours = $totalDailyWorkHours;
            }

            $report['daily_data'][] = [
                'date' => $date,
                'work_hours' => $dailyWorkHours,
                'overtime_hours' => $dailyOvertimeHours,
                'vacation_hours' => $dailyVacationHours,
                'sick_leave_hours' => $dailySickLeaveHours,
                'other_hours' => $dailyOtherHours,
            ];

            $report['totals']['work_hours'] += $dailyWorkHours;
            $report['totals']['overtime_hours'] += $dailyOvertimeHours;
            $report['totals']['vacation_hours'] += $dailyVacationHours;
            $report['totals']['sick_leave_hours'] += $dailySickLeaveHours;
            $report['totals']['other_hours'] += $dailyOtherHours;
        }

        return $report;
    }
}
