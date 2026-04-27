<?php

namespace Amicus\FilamentEmployeeManagement\Mail;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestType;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestStatusNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public LeaveRequest $leaveRequest
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $typeLabel = mb_strtolower($this->leaveRequest->type->getLabel());

        $subject = match ($this->leaveRequest->status) {
            LeaveRequestStatus::APPROVED => "Dopust odobren: {$typeLabel}",
            LeaveRequestStatus::REJECTED => "Dopust odbijen: {$typeLabel}",
            default => "Dopust – promjena statusa: {$typeLabel}",
        };

        return new Envelope(
            to: $this->leaveRequest->employee->email,
            cc: $this->resolveCc(),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'filament-employee-management::emails.leave-request-status',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if ($this->leaveRequest->status === LeaveRequestStatus::APPROVED
            && $this->leaveRequest->type === LeaveRequestType::ANNUAL_LEAVE
            && $this->leaveRequest->pdf_path
        ) {
            return [Attachment::fromStorageDisk('local', $this->leaveRequest->pdf_path)];
        }

        return [];
    }

    /**
     * @return array<int, string>
     */
    private function resolveCc(): array
    {
        $approverId = app(HumanResourcesSettings::class)->employee_work_hours_approver_id;

        if (! $approverId || $approverId === $this->leaveRequest->employee_id) {
            return [];
        }

        $approver = Employee::find($approverId);

        if (! $approver?->email) {
            return [];
        }

        return [$approver->email];
    }
}
