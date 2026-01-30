<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class EmployeeMonthlyHoursReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (config('employee-management.telegram-bot-api.is_active') && $notifiable->telegram_chat_id) {
            $channels[] = 'telegram';
        }

        return $channels;
    }

    public function toTelegram(object $notifiable): ?TelegramMessage
    {
        if (! config('employee-management.telegram-bot-api.is_active')) {
            return null;
        }

        if (! $notifiable->telegram_chat_id) {
            return null;
        }

        $url = EmployeeResource::getUrl('view', ['record' => $notifiable->id]) . '?tab=info';
        $message = TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("Izvještaj radnih sati\n\n" .
                'Molimo vas da do kraja radnog dana potvrdite radne sate za tekući mjesec kako bi vam se mogla izdati plaća.')
            ->button('Otvori izvještaj', $url);

        return $message;
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Izvještaj radnih sati')
            ->body('Molimo vas da do kraja radnog dana potvrdite radne sate za tekući mjesec kako bi vam se mogla izdati plaća.')
            ->getDatabaseMessage();
    }
}
