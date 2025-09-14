<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Mail\LeaveRequestStatusNotification;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Filament\Notifications\Notification as FilamentNotification;
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
        return ['mail', 'telegram', 'database'];
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
        $status = $this->leaveRequest->status->getLabel();
        $startDate = $this->leaveRequest->start_date->format('d.m.Y');
        $endDate = $this->leaveRequest->end_date->format('d.m.Y');

        $message = TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("Zahtjev za godišnji ({$startDate} - {$endDate}) ima novi status: {$status} \n\n Razlog: {$this->leaveRequest->rejection_reason}");

        return $message;
    }

    public function toDatabase(object $notifiable): array
    {
        $status = $this->leaveRequest->status->getLabel();
        $startDate = $this->leaveRequest->start_date->format('d.m.Y');
        $endDate = $this->leaveRequest->end_date->format('d.m.Y');

        return FilamentNotification::make()
            ->title('Zahtjev za godišnji ažuriran')
            ->body("Zahtjev za godišnji ({$startDate} - {$endDate}) ima novi status: {$status}\nRazlog: {$this->leaveRequest->rejection_reason}")
            ->getDatabaseMessage();
    }
}
