<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Observers\MonthlyWorkReportObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([MonthlyWorkReportObserver::class])]
class MonthlyWorkReport extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'for_month',
        'submitted_at',
        'submitted_by_user_id',
        'approved_at',
        'denied_at',
        'deny_reason',
        'total_available_hours',
        'work_hours',
        'overtime_hours',
        'vacation_hours',
        'sick_leave_hours',
        'other_hours',
        'holiday_hours',
    ];

    protected $casts = [
        'for_month' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'denied_at' => 'datetime',
        'total_available_hours' => 'decimal:2',
        'work_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'vacation_hours' => 'decimal:2',
        'sick_leave_hours' => 'decimal:2',
        'other_hours' => 'decimal:2',
        'holiday_hours' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'submitted_by_user_id');
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function isLocked(): bool
    {
        return $this->isSubmitted() || $this->isApproved();
    }

    public static function isMonthLocked(int $employeeId, Carbon $month): bool
    {
        $report = self::where('employee_id', $employeeId)
            ->where('for_month', $month->startOfMonth()->toDateString())
            ->first();

        return $report?->isLocked() ?? false;
    }

    /**
     * @param  array{available_hours: float, work_hours: float, overtime_hours: float, vacation_hours: float, sick_leave_hours: float, other_hours: float, holiday_hours: float}  $totals
     */
    public static function submitForReview(Employee $employee, Carbon $forMonth, array $totals, int $submittedByUserId): self
    {
        $report = self::firstOrNew([
            'employee_id' => $employee->id,
            'for_month' => $forMonth->startOfMonth()->toDateString(),
        ]);

        $report->fill([
            'total_available_hours' => $totals['available_hours'],
            'work_hours' => $totals['work_hours'],
            'overtime_hours' => $totals['overtime_hours'],
            'vacation_hours' => $totals['vacation_hours'],
            'sick_leave_hours' => $totals['sick_leave_hours'],
            'other_hours' => $totals['other_hours'],
            'holiday_hours' => $totals['holiday_hours'],
            'submitted_at' => now(),
            'submitted_by_user_id' => $submittedByUserId,
        ]);

        $report->save();

        return $report;
    }

    /**
     * @param  array{available_hours: float, work_hours: float, overtime_hours: float, vacation_hours: float, sick_leave_hours: float, other_hours: float, holiday_hours: float}  $totals
     */
    public static function approveAndLock(Employee $employee, Carbon $forMonth, array $totals): self
    {
        $report = self::firstOrNew([
            'employee_id' => $employee->id,
            'for_month' => $forMonth->startOfMonth()->toDateString(),
        ]);

        $report->fill([
            'total_available_hours' => $totals['available_hours'],
            'work_hours' => $totals['work_hours'],
            'overtime_hours' => $totals['overtime_hours'],
            'vacation_hours' => $totals['vacation_hours'],
            'sick_leave_hours' => $totals['sick_leave_hours'],
            'other_hours' => $totals['other_hours'],
            'holiday_hours' => $totals['holiday_hours'],
            'submitted_at' => $report->submitted_at ?? now(),
            'approved_at' => now(),
        ]);

        $report->save();

        return $report;
    }

    public function returnForCorrection(): void
    {
        $this->submitted_at = null;
        $this->submitted_by_user_id = null;
        $this->save();
    }
}
