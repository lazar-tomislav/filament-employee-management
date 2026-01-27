<?php

namespace Amicus\FilamentEmployeeManagement\Mail;

use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Podsjetnik: Zahtjev za odsustvo čeka odobrenje',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'filament-employee-management::emails.leave-request-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
