<?php

namespace Amicus\FilamentEmployeeManagement\Mail;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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
            LeaveRequestStatus::APPROVED => "Vaš zahtjev za dopust ({$typeLabel}) je odobren",
            LeaveRequestStatus::REJECTED => "Vaš zahtjev za dopust ({$typeLabel}) je odbijen",
            default => "Status vašeg zahtjeva za dopust ({$typeLabel}) je ažuriran",
        };

        return new Envelope(
            to: $this->leaveRequest->employee->email,
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
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
