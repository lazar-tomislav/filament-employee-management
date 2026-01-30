<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Mail\LeaveRequestFinalDecisionForHodMail;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveRequestFinalDecisionForHodNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): LeaveRequestFinalDecisionForHodMail
    {
        return (new LeaveRequestFinalDecisionForHodMail($this->leaveRequest))
            ->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        $employee = $this->leaveRequest->employee;
        $status = $this->leaveRequest->status->getLabel();
        $startDate = $this->leaveRequest->start_date->format('d.m.Y');
        $endDate = $this->leaveRequest->end_date->format('d.m.Y');

        return FilamentNotification::make()
            ->title("Finalna odluka: {$status}")
            ->body("Zahtjev zaposlenika {$employee->full_name} ({$startDate} - {$endDate}) je {$status}.")
            ->getDatabaseMessage();
    }
}
