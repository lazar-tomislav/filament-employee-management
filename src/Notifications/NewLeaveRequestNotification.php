<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource;
use Amicus\FilamentEmployeeManagement\Mail\LeaveRequestApprovalNotification;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NewLeaveRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'telegram'];
    }

    public function toMail(object $notifiable): LeaveRequestApprovalNotification
    {
        return (new LeaveRequestApprovalNotification($this->leaveRequest))
            ->to($notifiable->email);
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        // TODO: Move this to config and then publish it
        //https://ink.test/admin/human-resources/employees/1?tab=absence
        $url = EmployeeResource::getUrl('view',[
            'record' => $this->leaveRequest->employee_id,
            'tab' => 'absence',
        ]);
        $message = TelegramMessage::create()
            ->to(config('employee-management.telegram-bot-api.general_notification'))
            ->content("📋 Novi zahtjev za godišnji odmor\n\n" .
                "Zaposlenik: {$this->leaveRequest->employee->full_name}\n" .
                "Period: {$this->leaveRequest->start_date->format('d.m.Y')} - {$this->leaveRequest->end_date->format('d.m.Y')}\n")
            ->button('Idi u aplikaciju', $url)
        ;

        return $message;
    }
}
