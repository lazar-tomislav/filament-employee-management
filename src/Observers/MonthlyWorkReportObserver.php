<?php

namespace Amicus\FilamentEmployeeManagement\Observers;

use Amicus\FilamentEmployeeManagement\Models\MonthlyWorkReport;
use Amicus\FilamentEmployeeManagement\Notifications\MonthlyWorkReportResponseNotification;
use Illuminate\Support\Facades\Notification;

class MonthlyWorkReportObserver
{
    /**
     * Handle the MonthlyWorkReport "updated" event.
     */
    public function updated(MonthlyWorkReport $monthlyWorkReport): void
    {
        if ($monthlyWorkReport->isDirty('denied_at') && $monthlyWorkReport->denied_at !== null) {
            if (!empty($monthlyWorkReport->deny_reason)) {
                // The recipient is hardcoded in the notification itself.
                // We just need a notifiable to send the notification.
                $notifiable = new class {
                    use \Illuminate\Notifications\Notifiable;
                    public function routeNotificationForTelegram() {
                        return config('employee-management.telegram-bot-api.hr_notification');
                    }
                };
                Notification::send($notifiable, new MonthlyWorkReportResponseNotification($monthlyWorkReport));
            }
        }
    }
}
