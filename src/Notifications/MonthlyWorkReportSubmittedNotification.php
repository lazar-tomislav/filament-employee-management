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

class MonthlyWorkReportSubmittedNotification extends Notification implements ShouldQueue
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
            ->subject("Mjesečni izvještaj poslan na pregled - {$employee->full_name}")
            ->greeting('Poštovani,')
            ->line("Zaposlenik {$employee->full_name} je potvrdio radne sate za {$month} i čeka pregled.")
            ->line('Molimo pregledajte izvještaj i odobrite ga.')
            ->action('Pregledaj izvještaj', $url)
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

        $employee = $this->monthlyWorkReport->employee;
        $month = $this->monthlyWorkReport->for_month->translatedFormat('F Y');

        return TelegramMessage::create()
            ->to($telegramChatId)
            ->content("Zaposlenik {$employee->full_name} je potvrdio radne sate za {$month} i čeka pregled.");
    }

    public function toDatabase(object $notifiable): array
    {
        $employee = $this->monthlyWorkReport->employee;
        $month = $this->monthlyWorkReport->for_month->translatedFormat('F Y');

        return FilamentNotification::make()
            ->title('Mjesečni izvještaj poslan na pregled')
            ->body("Zaposlenik {$employee->full_name} je potvrdio radne sate za {$month} i čeka pregled.")
            ->getDatabaseMessage();
    }
}
