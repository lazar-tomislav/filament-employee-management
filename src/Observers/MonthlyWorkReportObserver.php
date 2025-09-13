<?php

namespace Amicus\FilamentEmployeeManagement\Observers;

use Amicus\FilamentEmployeeManagement\Models\MonthlyWorkReport;
use Amicus\FilamentEmployeeManagement\Notifications\MonthlyWorkReportResponseNotification;
use App\Models\User;
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
                User::allAdministrativeUsers()->each(function (User $user) use ($monthlyWorkReport) {
                    $user->notify(new MonthlyWorkReportResponseNotification($monthlyWorkReport));
                });
            }
        }
    }
}
