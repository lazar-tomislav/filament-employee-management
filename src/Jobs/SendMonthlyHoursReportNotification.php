<?php

namespace Amicus\FilamentEmployeeManagement\Jobs;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Notifications\EmployeeMonthlyHoursReportNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMonthlyHoursReportNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // we're taking the first employee because over him we're sending to channel where everyone is subscribed
        // so we dont need to send it to all employees 1 by 1, one is enough
        Employee::first()->notify(new EmployeeMonthlyHoursReportNotification());
    }
}
