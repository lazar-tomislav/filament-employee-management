<?php

namespace Amicus\FilamentEmployeeManagement\Commands;

use Amicus\FilamentEmployeeManagement\Jobs\SendMonthlyHoursReportNotification;
use Illuminate\Console\Command;

class TestMonthlyReportNotificationCommand extends Command
{
    protected $signature = 'employee:test-monthly-report';

    protected $description = 'Test sending the monthly hours report notification.';

    public function handle(): int
    {
        $this->info('Sending test notification...');
        
        try {
            dispatch(new SendMonthlyHoursReportNotification());
            $this->info('✅ Notification job dispatched successfully!');
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Greška: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
