<?php

namespace Amicus\FilamentEmployeeManagement\Mail;

use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestPendingHodApprovalMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novi zahtjev za odsustvo - potrebno vaše odobrenje',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'filament-employee-management::emails.leave-request-pending-hod-approval',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
