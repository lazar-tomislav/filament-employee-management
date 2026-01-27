<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Mail\LeaveRequestReminderMail;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveRequestReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): LeaveRequestReminderMail
    {
        return (new LeaveRequestReminderMail($this->leaveRequest))
            ->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        $employee = $this->leaveRequest->employee;
        $startDate = $this->leaveRequest->start_date->format('d.m.Y');
        $endDate = $this->leaveRequest->end_date->format('d.m.Y');

        return FilamentNotification::make()
            ->title('Podsjetnik: Zahtjev za odsustvo čeka odobrenje')
            ->body("Zaposlenik: {$employee->full_name}\nPeriod: {$startDate} - {$endDate}")
            ->getDatabaseMessage();
    }
}
