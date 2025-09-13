<?php

namespace Amicus\FilamentEmployeeManagement\Observers;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Notifications\LeaveRequestStatusChangeNotification;
use Amicus\FilamentEmployeeManagement\Notifications\NewLeaveRequestNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LeaveRequestObserver
{
    /**
     * Handle the LeaveRequest "created" event.
     */
    public function created(LeaveRequest $leaveRequest): void
    {
        User::allAdministrativeUsers()
            ->filter() // Remove null values
            ->each(function (User $user) use ($leaveRequest) {
                $user->employee->notify(new NewLeaveRequestNotification($leaveRequest));
            });
    }

    /**
     * Handle the LeaveRequest "updated" event.
     */
    public function updated(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->isDirty('status')) {

            if ($leaveRequest->status === LeaveRequestStatus::CANCELED->value) {
                Log::info("Leave request $leaveRequest->id has been canceled.");
                return;
            }
            $leaveRequest->employee->notify(new LeaveRequestStatusChangeNotification($leaveRequest));
        }
    }
}
