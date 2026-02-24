<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestType;
use Amicus\FilamentEmployeeManagement\Observers\LeaveRequestObserver;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'approved_by_head_of_department_id',
        'approved_by_director_id',
        'approved_by_head_of_department_at',
        'approved_by_director_at',
        'pdf_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_count' => 'integer',
        'type' => LeaveRequestType::class,
        'status' => LeaveRequestStatus::class,
        'approved_by_head_of_department_at' => 'datetime',
        'approved_by_director_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function headOfDepartmentApprover(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by_head_of_department_id');
    }

    public function directorApprover(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by_director_id');
    }

    public function leaveAllowance(): BelongsTo
    {
        return $this->belongsTo(LeaveAllowance::class);
    }

    protected function absence(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->start_date->format('d.m.Y') . ' - ' . $this->end_date->format('d.m.Y'),
        );
    }

    public function isApprovedByHeadOfDepartment(): bool
    {
        return $this->approved_by_head_of_department_id !== null;
    }

    public function isApprovedByDirector(): bool
    {
        return $this->approved_by_director_id !== null;
    }

    /**
     * Provjerava treba li zahtjev odobrenje voditelja odjela.
     * Vraća false ako:
     * - Zaposlenik nema odjel
     * - Odjel nema voditelja
     * - Zaposlenik JE direktor
     * - Zaposlenik JE voditelj svog odjela (ne treba sam sebi odobravati)
     */
    public function requiresHeadOfDepartmentApproval(): bool
    {
        $employee = $this->employee;

        if (! $employee) {
            return false;
        }

        $directorId = app(HumanResourcesSettings::class)->employee_director_id;

        if ($employee->id === $directorId) {
            return false;
        }

        $department = $employee->department;

        if (! $department) {
            return false;
        }

        if (! $department->head_of_department_employee_id) {
            return false;
        }

        if ($department->head_of_department_employee_id === $employee->id) {
            return false;
        }

        return true;
    }

    /**
     * Provjerava može li dani zaposlenik odobriti zahtjev kao voditelj odjela.
     */
    public function canBeApprovedByHeadOfDepartment(Employee $approver): bool
    {
        if ($this->status !== LeaveRequestStatus::PENDING) {
            return false;
        }

        if ($this->isApprovedByHeadOfDepartment()) {
            return false;
        }

        if (! $this->requiresHeadOfDepartmentApproval()) {
            return false;
        }

        $employee = $this->employee;
        $department = $employee?->department;

        if (! $department) {
            return false;
        }

        return $department->head_of_department_employee_id === $approver->id;
    }

    /**
     * Provjerava može li dani zaposlenik odobriti zahtjev kao direktor.
     */
    public function canBeApprovedByDirector(Employee $approver): bool
    {
        if ($this->status !== LeaveRequestStatus::PENDING) {
            return false;
        }

        $directorId = app(HumanResourcesSettings::class)->employee_director_id;

        if ($approver->id !== $directorId) {
            return false;
        }

        if ($this->requiresHeadOfDepartmentApproval() && ! $this->isApprovedByHeadOfDepartment()) {
            return false;
        }

        return true;
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
