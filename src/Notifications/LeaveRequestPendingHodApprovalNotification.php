<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Mail\LeaveRequestPendingHodApprovalMail;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveRequestPendingHodApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): LeaveRequestPendingHodApprovalMail
    {
        return (new LeaveRequestPendingHodApprovalMail($this->leaveRequest))
            ->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        $employee = $this->leaveRequest->employee;
        $startDate = $this->leaveRequest->start_date->format('d.m.Y');
        $endDate = $this->leaveRequest->end_date->format('d.m.Y');

        return FilamentNotification::make()
            ->title('Novi zahtjev za odsustvo - potrebno odobrenje')
            ->body("Zaposlenik: {$employee->full_name}\nPeriod: {$startDate} - {$endDate}")
            ->getDatabaseMessage();
    }
}
