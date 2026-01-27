<?php

namespace Amicus\FilamentEmployeeManagement\Observers;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Notifications\LeaveRequestPendingDirectorApprovalNotification;
use Amicus\FilamentEmployeeManagement\Notifications\LeaveRequestPendingHodApprovalNotification;
use Amicus\FilamentEmployeeManagement\Notifications\LeaveRequestStatusChangeNotification;
use Amicus\FilamentEmployeeManagement\Services\LeaveRequestPdfService;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use Illuminate\Support\Facades\Log;

class LeaveRequestObserver
{
    /**
     * Handle the LeaveRequest "created" event.
     */
    public function created(LeaveRequest $leaveRequest): void
    {
        $settings = app(HumanResourcesSettings::class);
        $directorId = $settings->employee_director_id;
        $employee = $leaveRequest->employee;

        if ($employee->id === $directorId) {
            $this->notifyDirector($leaveRequest, $directorId, afterHodApproval: false);

            return;
        }

        if ($leaveRequest->requiresHeadOfDepartmentApproval()) {
            $this->notifyHeadOfDepartment($leaveRequest);

            return;
        }

        $this->notifyDirector($leaveRequest, $directorId, afterHodApproval: false);
    }

    /**
     * Handle the LeaveRequest "updated" event.
     */
    public function updated(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->isDirty('approved_by_head_of_department_id') && $leaveRequest->approved_by_head_of_department_id !== null) {
            $settings = app(HumanResourcesSettings::class);
            $this->notifyDirector($leaveRequest, $settings->employee_director_id, afterHodApproval: true);
        }

        if ($leaveRequest->isDirty('status')) {
            if ($leaveRequest->status === LeaveRequestStatus::CANCELED->value) {
                Log::info("Leave request $leaveRequest->id has been canceled.");

                return;
            }

            if ($leaveRequest->status === LeaveRequestStatus::APPROVED->value) {
                $pdfPath = LeaveRequestPdfService::generatePdf($leaveRequest);
                $leaveRequest->updateQuietly(['pdf_path' => $pdfPath]);
            }

            if (in_array($leaveRequest->status, [LeaveRequestStatus::APPROVED->value, LeaveRequestStatus::REJECTED->value])) {
                $leaveRequest->employee->notify(new LeaveRequestStatusChangeNotification($leaveRequest));
            }
        }
    }

    private function notifyHeadOfDepartment(LeaveRequest $leaveRequest): void
    {
        $department = $leaveRequest->employee?->department;

        if (! $department || ! $department->head_of_department_employee_id) {
            Log::warning("Cannot notify head of department for leave request {$leaveRequest->id}: no department or head of department set.");

            return;
        }

        $headOfDepartment = Employee::find($department->head_of_department_employee_id);

        if (! $headOfDepartment) {
            Log::warning("Cannot notify head of department for leave request {$leaveRequest->id}: head of department employee not found.");

            return;
        }

        if (! $headOfDepartment->user) {
            Log::warning("Cannot notify head of department for leave request {$leaveRequest->id}: head of department has no associated user.");

            return;
        }

        $headOfDepartment->user->notify(new LeaveRequestPendingHodApprovalNotification($leaveRequest));
    }

    private function notifyDirector(LeaveRequest $leaveRequest, ?int $directorId, bool $afterHodApproval): void
    {
        if (! $directorId) {
            Log::warning("Cannot notify director for leave request {$leaveRequest->id}: director not configured in HR settings.");

            return;
        }

        $director = Employee::find($directorId);

        if (! $director) {
            Log::warning("Cannot notify director for leave request {$leaveRequest->id}: director employee not found.");

            return;
        }

        if (! $director->user) {
            Log::warning("Cannot notify director for leave request {$leaveRequest->id}: director has no associated user.");

            return;
        }

        $director->user->notify(new LeaveRequestPendingDirectorApprovalNotification($leaveRequest, $afterHodApproval));
    }
}
