<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Database\Factories\EmployeeFactory;
use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestType;
use Amicus\FilamentEmployeeManagement\Observers\EmployeeObserver;
use Amicus\FilamentEmployeeManagement\Traits\HasEmployeeRole;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy([EmployeeObserver::class])]
class Employee extends Model
{
    use HasEmployeeRole;
    use HasFactory;
    use HasRoles;
    use SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): EmployeeFactory
    {
        return EmployeeFactory::new();
    }

    const HOURS_PER_WORK_DAY = 8;

    const WORK_DAYS = [
        Carbon::MONDAY,
        Carbon::TUESDAY,
        Carbon::WEDNESDAY,
        Carbon::THURSDAY,
        Carbon::FRIDAY,
    ];

    protected $fillable = [
        'telegram_denied_at',
        'telegram_chat_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone_numbers',
        'password',
        'address',
        'city',
        'oib',
        'note',
        'is_active',
        'role',
        'department_id',
        'job_position',
        'signature_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'telegram_denied_at' => 'datetime',
        'phone_numbers' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            //            $builder->where('is_active', true);
        });
    }

    protected function telegramChatId(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
        );
    }

    public function leaveAllowances()
    {
        return $this->hasMany(LeaveAllowance::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function routeNotificationForTelegram()
    {
        return $this->telegram_chat_id;
    }

    protected function fullName(): Attribute
    {
        if ($this->is_active && $this->deleted_at == null) {
            return Attribute::make(
                get: fn () => "{$this->first_name} {$this->last_name}",
            );
        }

        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name} (NEAKTIVAN / OBRISAN)",
        );
    }

    protected function fullNameEmail(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->full_name} ({$this->email})",
        );
    }

    protected function initials(): Attribute
    {
        return Attribute::make(
            get: fn () => strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1)),
        );
    }

    public static function options()
    {
        // pluck all to array (first_name, last name (email) as value) and id as key
        return self::query()->withTrashed()->get()->pluck(function ($employee) {
            return $employee->full_name;
        }, 'id');
    }

    public function getMonthlyWorkReport(Carbon $month): array
    {
        $report = [
            'daily_data' => [],
            'totals' => [
                'work_hours' => 0.0,
                'work_from_home_hours' => 0.0,
                'overtime_hours' => 0.0,
                'vacation_hours' => 0.0,
                'sick_leave_hours' => 0.0,
                'other_hours' => 0.0,
                'maternity_leave_hours' => 0.0,
                'holiday_hours' => 0.0,
                'available_hours' => 0.0,
            ],
        ];

        $daysInMonth = $month->daysInMonth;
        $holidays = Holiday::getHolidaysForMonth($month);

        $timeLogsByDate = $this->timeLogs()
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->selectRaw('date, SUM(hours) as total_hours, SUM(CASE WHEN is_work_from_home = 1 THEN hours ELSE 0 END) as wfh_hours, MIN(work_start_time) as work_start_time, MAX(work_end_time) as work_end_time')
            ->groupBy('date')
            ->get()
            ->keyBy(fn ($log) => Carbon::parse($log->date)->format('Y-m-d'));

        $leaveRequestsCursor = $this->leaveRequests()
            ->where(function ($query) use ($month) {
                $query->where(function ($q) use ($month) {
                    $q->whereYear('start_date', $month->year)->whereMonth('start_date', $month->month);
                })->orWhere(function ($q) use ($month) {
                    $q->whereYear('end_date', $month->year)->whereMonth('end_date', $month->month);
                });
            })
            ->cursor();

        $leaveRequestsByDate = [];
        foreach ($leaveRequestsCursor as $leaveRequest) {
            $currentDate = Carbon::parse($leaveRequest->start_date);
            $endDate = Carbon::parse($leaveRequest->end_date);
            while ($currentDate->lte($endDate)) {
                if ($currentDate->month == $month->month && $currentDate->year == $month->year) {
                    $leaveRequestsByDate[$currentDate->format('Y-m-d')] = $leaveRequest;
                }
                $currentDate->addDay();
            }
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $month->copy()->setDay($day);
            $dateString = $date->format('Y-m-d');

            $dailyLog = $timeLogsByDate->get($dateString);
            $totalDailyWorkHours = (float) ($dailyLog->total_hours ?? 0);
            $totalDailyWorkFromHomeHours = (float) ($dailyLog->wfh_hours ?? 0);

            $dailyWorkHours = 0.0;
            $dailyWorkFromHomeHours = 0.0;
            $dailyOvertimeHours = 0.0;
            $dailyVacationHours = 0.0;
            $dailySickLeaveHours = 0.0;
            $dailyOtherHours = 0.0;
            $dailyMaternityLeaveHours = 0.0;
            $dailyHolidayHours = 0.0;

            $leaveRequest = $leaveRequestsByDate[$dateString] ?? null;
            if ($leaveRequest && in_array($date->dayOfWeek, self::WORK_DAYS)) {
                $hourType = match ($leaveRequest->type) {
                    LeaveRequestType::ANNUAL_LEAVE => 'dailyVacationHours',
                    LeaveRequestType::SICK_LEAVE => 'dailySickLeaveHours',
                    LeaveRequestType::PAID_LEAVE => 'dailyOtherHours',
                    LeaveRequestType::MATERNITY_LEAVE => 'dailyMaternityLeaveHours',
                    default => null,
                };
                if ($hourType) {
                    $$hourType = self::HOURS_PER_WORK_DAY;
                }
            }

            $isWorkDayOfWeek = in_array($date->dayOfWeek, self::WORK_DAYS);
            $isPublicHoliday = in_array($dateString, $holidays);

            if ($isPublicHoliday && $isWorkDayOfWeek) {
                $dailyVacationHours = 0;
                $dailySickLeaveHours = 0;
                $dailyOtherHours = 0;
                $dailyMaternityLeaveHours = 0;
                $dailyHolidayHours = self::HOURS_PER_WORK_DAY;
            }

            $isOnLeave = $dailyVacationHours > 0 || $dailySickLeaveHours > 0 || $dailyOtherHours > 0 || $dailyMaternityLeaveHours > 0;

            if ($isWorkDayOfWeek && ! $isPublicHoliday) {
                $report['totals']['available_hours'] += self::HOURS_PER_WORK_DAY;
            }

            if ($isWorkDayOfWeek && ! $isPublicHoliday && ! $isOnLeave) {
                // Separate regular work hours from WFH hours
                $totalRegularHours = $totalDailyWorkHours - $totalDailyWorkFromHomeHours;

                if ($totalDailyWorkHours > self::HOURS_PER_WORK_DAY) {
                    // Cap regular work at remaining capacity after WFH
                    $dailyWorkHours = max(0, self::HOURS_PER_WORK_DAY - $totalDailyWorkFromHomeHours);
                    $dailyOvertimeHours = $totalDailyWorkHours - self::HOURS_PER_WORK_DAY;
                } else {
                    $dailyWorkHours = $totalRegularHours;
                }
                $dailyWorkFromHomeHours = $totalDailyWorkFromHomeHours;
            } else {
                $dailyOvertimeHours = $totalDailyWorkHours;
            }

            $report['daily_data'][] = [
                'date' => $date,
                'work_hours' => $dailyWorkHours,
                'work_from_home_hours' => $dailyWorkFromHomeHours,
                'overtime_hours' => $dailyOvertimeHours,
                'vacation_hours' => $dailyVacationHours,
                'sick_leave_hours' => $dailySickLeaveHours,
                'other_hours' => $dailyOtherHours,
                'maternity_leave_hours' => $dailyMaternityLeaveHours,
                'holiday_hours' => $dailyHolidayHours,
                'is_holiday' => $isPublicHoliday,
                'total_hours' => $totalDailyWorkHours,
                'total_wfh_hours' => $totalDailyWorkFromHomeHours,
                'is_weekend' => ! in_array($date->dayOfWeek, self::WORK_DAYS),
                'work_start_time' => $dailyLog->work_start_time ?? null,
                'work_end_time' => $dailyLog->work_end_time ?? null,
            ];

            $report['totals']['work_hours'] += $dailyWorkHours;
            $report['totals']['work_from_home_hours'] += $dailyWorkFromHomeHours;
            $report['totals']['overtime_hours'] += $dailyOvertimeHours;
            $report['totals']['vacation_hours'] += $dailyVacationHours;
            $report['totals']['sick_leave_hours'] += $dailySickLeaveHours;
            $report['totals']['other_hours'] += $dailyOtherHours;
            $report['totals']['maternity_leave_hours'] += $dailyMaternityLeaveHours;
            $report['totals']['holiday_hours'] += $dailyHolidayHours;
        }

        return $report;
    }
}
