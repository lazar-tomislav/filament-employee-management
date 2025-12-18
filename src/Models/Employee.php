<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Database\Factories\EmployeeFactory;
use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestType;
use Amicus\FilamentEmployeeManagement\Observers\EmployeeObserver;
use Amicus\FilamentEmployeeManagement\Traits\HasEmployeeRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy([EmployeeObserver::class])]
class Employee extends Model
{
    use HasEmployeeRole;
    use HasFactory;
    use HasRoles;
    use Notifiable;
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
        'phone_number',
        'password',
        'address',
        'city',
        'oib',
        'note',
        'is_active',
        'role',
        'department_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'telegram_denied_at' => 'datetime',
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

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
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

        if (! in_array($date->dayOfWeek, self::WORK_DAYS)) {
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
            } else {
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

            $isStandardWorkDay = $isWorkDayOfWeek && ! $isPublicHoliday && ! $isOnLeave;

            // Available hours are calculated based on potential work days (ignoring personal leave)
            if ($isWorkDayOfWeek && ! $isPublicHoliday) {
                $report['totals']['available_hours'] += self::HOURS_PER_WORK_DAY;
            }

            if ($isStandardWorkDay) {
                $dailyWorkHours = $totalDailyWorkHours;
                if ($totalDailyWorkHours > self::HOURS_PER_WORK_DAY) {
                    $dailyWorkHours = self::HOURS_PER_WORK_DAY;
                    $dailyOvertimeHours = $totalDailyWorkHours - self::HOURS_PER_WORK_DAY;
                }
            } elseif ($isPublicHoliday) {
                // For holidays, count as 8 work hours, plus any logged hours as overtime
                $dailyWorkHours = self::HOURS_PER_WORK_DAY;
                $dailyOvertimeHours = $totalDailyWorkHours;
            } else { // It's a weekend or a personal leave day
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

    public function taskUpdates(): HasMany
    {
        return $this->hasMany(\Amicus\FilamentEmployeeManagement\Models\TaskUpdate::class, 'employee_id');
    }

    public function mentionsInTaskUpdates(): BelongsToMany
    {
        return $this->belongsToMany(
            \Amicus\FilamentEmployeeManagement\Models\TaskUpdate::class,
            'task_update_mentions',
            'mentioned_employee_id',
            'task_update_id'
        );
    }

    public function assignedOffers(): ?HasMany
    {
        // if model_exists
        if (class_exists(\App\Models\Offer::class)) {
            return $this->hasMany(\App\Models\Offer::class, 'assigned_to');
        }

        return null;
    }

    /**
     * Override notify method to also send notification to associated user
     * for Filament panel notifications
     */
    public function notify($instance)
    {
        try {
            // Send notification to employee using Notifiable trait method
            parent::notify($instance);

            // Also send to associated user for Filament panel
            $user = $this->user; // Cachiramo referencu
            if ($user && method_exists($user, 'notify')) {

                if ($instance->id == null) {
                    return;
                }
                $user->notify($instance);
            }
        } catch (\Exception $e) {
            report($e);
            Log::error("Failed to notify employee {$this->id} ({$this->full_name}): {$e->getMessage()}");
        }
    }
}
