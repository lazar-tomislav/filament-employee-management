<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Pages\ViewEmployee;
use Amicus\FilamentEmployeeManagement\Models\MonthlyWorkReport;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class MonthlyWorkReportReturnedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MonthlyWorkReport $monthlyWorkReport
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];

        if (config('employee-management.telegram-bot-api.is_active') && $notifiable->telegram_chat_id) {
            $channels[] = 'telegram';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $employee = $this->monthlyWorkReport->employee;
        $month = $this->monthlyWorkReport->for_month->translatedFormat('F Y');
        $url = ViewEmployee::getUrl(['record' => $employee->id]) . '?tab=monthly_report';

        return (new MailMessage)
            ->subject("Mjesečni izvještaj vraćen na ispravak - {$month}")
            ->greeting('Poštovani,')
            ->line("Vaš mjesečni izvještaj za {$month} je vraćen na ispravak.")
            ->line('Molimo pregledajte i ponovno pošaljite izvještaj.')
            ->action('Ispravi izvještaj', $url)
            ->salutation('Lijep pozdrav');
    }

    public function toTelegram(object $notifiable): ?TelegramMessage
    {
        if (! config('employee-management.telegram-bot-api.is_active')) {
            return null;
        }

        $telegramChatId = $notifiable->telegram_chat_id;

        if (! $telegramChatId) {
            return null;
        }

        $month = $this->monthlyWorkReport->for_month->translatedFormat('F Y');

        return TelegramMessage::create()
            ->to($telegramChatId)
            ->content("Mjesečni izvještaj za {$month} je vraćen na ispravak. Molimo pregledajte i ponovno pošaljite.");
    }

    public function toDatabase(object $notifiable): array
    {
        $month = $this->monthlyWorkReport->for_month->translatedFormat('F Y');

        return FilamentNotification::make()
            ->title('Mjesečni izvještaj vraćen na ispravak')
            ->body("Mjesečni izvještaj za {$month} je vraćen na ispravak. Molimo pregledajte i ponovno pošaljite.")
            ->getDatabaseMessage();
    }
}
