<?php

namespace Amicus\FilamentEmployeeManagement\Mail;

use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestFinalDecisionForHodMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {}

    public function envelope(): Envelope
    {
        $status = $this->leaveRequest->status->getLabel();

        return new Envelope(
            subject: "Zahtjev za odsustvo - finalna odluka: {$status}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'filament-employee-management::emails.leave-request-final-decision-hod',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
