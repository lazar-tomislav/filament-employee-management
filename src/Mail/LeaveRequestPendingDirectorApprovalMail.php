<?php

namespace Amicus\FilamentEmployeeManagement\Mail;

use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestPendingDirectorApprovalMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public bool $afterHodApproval = false
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->afterHodApproval
            ? 'Zahtjev za odsustvo odobren od voditelja - potrebno finalno odobrenje'
            : 'Zahtjev za odsustvo - potrebno finalno odobrenje';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'filament-employee-management::emails.leave-request-pending-director-approval',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
