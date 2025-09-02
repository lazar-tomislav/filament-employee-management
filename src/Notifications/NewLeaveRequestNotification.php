<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Mail\LeaveRequestApprovalNotification;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewLeaveRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): LeaveRequestApprovalNotification
    {
        return (new LeaveRequestApprovalNotification($this->leaveRequest))
            ->to($notifiable->email);
    }
}
