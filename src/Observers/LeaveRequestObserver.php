<?php

namespace Amicus\FilamentEmployeeManagement\Observers;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Mail\LeaveRequestApprovalNotification;
use Amicus\FilamentEmployeeManagement\Mail\LeaveRequestStatusNotification;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Illuminate\Support\Facades\Mail;

class LeaveRequestObserver
{
    /**
     * Handle the LeaveRequest "created" event.
     */
    public function created(LeaveRequest $leaveRequest): void
    {
        // TODO: Replace with actual admin email
        $adminEmail = 'admin@example.com';

        Mail::to($adminEmail)->send(new LeaveRequestApprovalNotification($leaveRequest));
    }

    /**
     * Handle the LeaveRequest "updated" event.
     */
    public function updated(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->isDirty('status')) {
            $employeeEmail = $leaveRequest->employee->email;

            if (! $employeeEmail) {
                return;
            }

            match ($leaveRequest->status) {
                LeaveRequestStatus::APPROVED->value, LeaveRequestStatus::REJECTED->value => Mail::to($employeeEmail)->send(new LeaveRequestStatusNotification($leaveRequest)),
                default => null,
            };
        }
    }
}
