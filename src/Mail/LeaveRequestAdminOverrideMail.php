<?php

declare(strict_types=1);

namespace Amicus\FilamentEmployeeManagement\Mail;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestAdminOverrideMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public string $reason,
        public string $statusKey,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->leaveRequest->employee->email,
            subject: 'Promjena statusa zahtjeva za godišnji odmor',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'filament-employee-management::emails.leave-request-admin-override',
            with: [
                'leaveRequest' => $this->leaveRequest,
                'reason' => $this->reason,
                'statusKey' => $this->statusKey,
                'workHoursApproverName' => $this->resolveWorkHoursApproverName(),
            ],
        );
    }

    private function resolveWorkHoursApproverName(): ?string
    {
        $approverId = app(HumanResourcesSettings::class)->employee_work_hours_approver_id;

        if (! $approverId) {
            return null;
        }

        return Employee::find($approverId)?->full_name;
    }
}
