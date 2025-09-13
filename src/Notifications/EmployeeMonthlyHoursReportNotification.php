<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Pages\ViewEmployee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class EmployeeMonthlyHoursReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
    }

    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram(object $notifiable): ?TelegramMessage
    {
        if(!$notifiable->telegram_chat_id){
            return null;
        }

        $url = EmployeeResource::getUrl('view', ['record' => $notifiable->id,]) . '?tab=info';
        $message = TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("Izvještaj radnih sati\n\n" .
                "Molimo vas da do kraja radnog dana potvrdite radne sate za tekući mjesec kako da vam se može izdati plaća.")
            ->button('Otvori izvještaj', $url);

        return $message;
    }
}
