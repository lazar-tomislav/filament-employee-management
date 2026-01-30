<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Mail\LeaveRequestPendingDirectorApprovalMail;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveRequestPendingDirectorApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public bool $afterHodApproval = false
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): LeaveRequestPendingDirectorApprovalMail
    {
        return (new LeaveRequestPendingDirectorApprovalMail($this->leaveRequest, $this->afterHodApproval))
            ->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        $employee = $this->leaveRequest->employee;
        $startDate = $this->leaveRequest->start_date->format('d.m.Y');
        $endDate = $this->leaveRequest->end_date->format('d.m.Y');

        $title = $this->afterHodApproval
            ? 'Zahtjev odobren od voditelja - potrebno finalno odobrenje'
            : 'Zahtjev za odsustvo - potrebno finalno odobrenje';

        $body = "Zaposlenik: {$employee->full_name}\nPeriod: {$startDate} - {$endDate}";

        if ($this->afterHodApproval && $this->leaveRequest->headOfDepartmentApprover) {
            $body .= "\nOdobrio voditelj: {$this->leaveRequest->headOfDepartmentApprover->full_name}";
        }

        return FilamentNotification::make()
            ->title($title)
            ->body($body)
            ->getDatabaseMessage();
    }
}
