<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource;
use Amicus\FilamentEmployeeManagement\Mail\LeaveRequestApprovalNotification;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NewLeaveRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest
    )
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'telegram', 'database'];
    }

    public function toMail(object $notifiable): LeaveRequestApprovalNotification
    {
        return (new LeaveRequestApprovalNotification($this->leaveRequest))
            ->to($notifiable->email);
    }

    public function toTelegram(object $notifiable): ?TelegramMessage
    {
        if (!config('employee-management.telegram-bot-api.is_active')) {
            return null;
        }

        if(!$notifiable->telegram_chat_id){
            return null;
        }
        $url = EmployeeResource::getUrl('view', [
            'record' => $this->leaveRequest->employee_id,
            'tab' => 'absence',
        ]);
        $message = TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("📋 Novi zahtjev za godišnji odmor\n\n" .
                "Zaposlenik: {$this->leaveRequest->employee->full_name}\n" .
                "Period: {$this->leaveRequest->start_date->format('d.m.Y')} - {$this->leaveRequest->end_date->format('d.m.Y')}\n")
            ->button('Idi u aplikaciju', $url);

        return $message;
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Novi zahtjev za godišnji odmor')
            ->body("Zaposlenik: {$this->leaveRequest->employee->full_name}\nPeriod: {$this->leaveRequest->start_date->format('d.m.Y')} - {$this->leaveRequest->end_date->format('d.m.Y')}")
            ->getDatabaseMessage();
    }
}
