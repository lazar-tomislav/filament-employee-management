<?php

namespace Amicus\FilamentEmployeeManagement\Observers;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Notifications\LeaveRequestStatusChangeNotification;
use Amicus\FilamentEmployeeManagement\Notifications\NewLeaveRequestNotification;
use Amicus\FilamentEmployeeManagement\Services\LeaveRequestPdfService;
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
                if($user->employee){
                    $user->employee->notify(new NewLeaveRequestNotification($leaveRequest));
                }else{
                    report(new \Exception("User {$user->id} does not have an associated employee record."));
                }
            });
    }

    /**
     * Handle the LeaveRequest "updated" event.
     */
    public function updated(LeaveRequest $leaveRequest): void
    {
        if($leaveRequest->isDirty('status')){

            if($leaveRequest->status === LeaveRequestStatus::CANCELED->value){
                Log::info("Leave request $leaveRequest->id has been canceled.");
                return;
            }

            // Generate PDF when leave request is approved
            if($leaveRequest->status === LeaveRequestStatus::APPROVED->value){
                $pdfPath = LeaveRequestPdfService::generatePdf($leaveRequest);
                $leaveRequest->update(['pdf_path' => $pdfPath]);
            }

            $leaveRequest->employee->notify(new LeaveRequestStatusChangeNotification($leaveRequest));
        }
    }
}
