<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Mail\LeaveAbsenceInfoMail;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveAbsenceInfoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): LeaveAbsenceInfoMail
    {
        return (new LeaveAbsenceInfoMail($this->leaveRequest))
            ->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        $employee = $this->leaveRequest->employee;
        $typeLabel = $this->leaveRequest->type->getLabel();
        $startDate = $this->leaveRequest->start_date->format('d.m.Y');
        $endDate = $this->leaveRequest->end_date->format('d.m.Y');

        return FilamentNotification::make()
            ->title("Obavijest o odsustvu: {$employee->full_name}")
            ->body("Zaposlenik {$employee->full_name} ima odobreno odsustvo ({$typeLabel}) od {$startDate} do {$endDate}.")
            ->getDatabaseMessage();
    }
}
