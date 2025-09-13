<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Mail\LeaveRequestStatusNotification;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class LeaveRequestStatusChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'telegram'];
    }

    public function toMail(object $notifiable): LeaveRequestStatusNotification
    {
        return new LeaveRequestStatusNotification($this->leaveRequest);
    }

    public function toTelegram(object $notifiable): ?TelegramMessage
    {
        if(!$notifiable->telegram_chat_id){
            return null;
        }
        $employee = $this->leaveRequest->employee;
        $status = $this->leaveRequest->status;
        $startDate = $this->leaveRequest->start_date->format('d.m.Y');
        $endDate = $this->leaveRequest->end_date->format('d.m.Y');

        $statusText = match($status) {
            'approved' => 'odobren',
            'rejected' => 'odbačen',
            default => 'ažuriran'
        };

        $message = TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("Zahtjev za godišnji odmor za zaposlenika {$employee->full_name} ({$startDate} - {$endDate}) je {$statusText}.");

        return $message;
    }
}
