<?php

namespace Amicus\FilamentEmployeeManagement\Mail;

use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveAbsenceInfoMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {}

    public function envelope(): Envelope
    {
        $employeeName = $this->leaveRequest->employee->full_name;
        $typeLabel = mb_strtolower($this->leaveRequest->type->getLabel());
        $startDate = $this->leaveRequest->start_date->format('d.m.Y');
        $endDate = $this->leaveRequest->end_date->format('d.m.Y');

        return new Envelope(
            subject: "Obavijest o odsustvu: {$employeeName} - {$typeLabel} ({$startDate} - {$endDate})",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'filament-employee-management::emails.leave-absence-info',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
