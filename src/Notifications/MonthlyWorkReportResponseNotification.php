<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Models\MonthlyWorkReport;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class MonthlyWorkReportResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MonthlyWorkReport $monthlyWorkReport
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['telegram', 'database'];
    }

    public function toTelegram(object $notifiable): ?TelegramMessage
    {
        $employee = $this->monthlyWorkReport->employee;
        if(!$employee->telegram_chat_id){
            return null;
        }
        $month = $this->monthlyWorkReport->for_month->format('m/Y');

        $message = TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("Izvještaj o radnim satima za zaposlenika {$employee->full_name} za mjesec {$month} je odbijen.\n\n" .
                "Razlog: {$this->monthlyWorkReport->deny_reason}");

        return $message;
    }

    public function toDatabase(object $notifiable): array
    {
        $employee = $this->monthlyWorkReport->employee;
        $month = $this->monthlyWorkReport->for_month->format('m/Y');

        return FilamentNotification::make()
            ->title('Izvještaj o radnim satima odbijen')
            ->body("Izvještaj za zaposlenika {$employee->full_name} za mjesec {$month} je odbijen.\nRazlog: {$this->monthlyWorkReport->deny_reason}")
            ->getDatabaseMessage();
    }
}
