<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Models\MonthlyWorkReport;
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
        return ['telegram'];
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $employee = $this->monthlyWorkReport->employee;
        $month = $this->monthlyWorkReport->for_month->format('m/Y');

        $message = TelegramMessage::create()
            ->to(config('employee-management.telegram-bot-api.hr_notification'))
            ->content("Izvještaj o radnim satima za zaposlenika {$employee->full_name} za mjesec {$month} je odbijen.\n\n" .
                "Razlog: {$this->monthlyWorkReport->deny_reason}");

        return $message;
    }
}
