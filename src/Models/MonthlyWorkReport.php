<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Observers\MonthlyWorkReportObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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
        'holiday_hours',
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
        'holiday_hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public static function updateReportStatus(Employee $employee, Carbon $forMonth, array $totals, bool $isApproved, ?string $denyReason = null): void
    {
        $report = self::firstOrNew([
            'employee_id' => $employee->id,
            'for_month' => $forMonth->startOfMonth()->toDateString(),
        ]);

        if (! $report->exists) {
            $report->total_available_hours = $totals['available_hours'];
            $report->work_hours = $totals['work_hours'];
            $report->overtime_hours = $totals['overtime_hours'];
            $report->vacation_hours = $totals['vacation_hours'];
            $report->sick_leave_hours = $totals['sick_leave_hours'];
            $report->other_hours = $totals['other_hours'];
            $report->holiday_hours = $totals['holiday_hours'];
        }

        if ($isApproved) {
            $report->approved_at = now();
            $report->denied_at = null;
            $report->deny_reason = null;
        } else {
            $report->approved_at = null;
            $report->denied_at = now();
            $report->deny_reason = $denyReason;
        }

        $report->save();
    }
}
