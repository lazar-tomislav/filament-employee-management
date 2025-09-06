<?php

namespace Amicus\FilamentEmployeeManagement\Commands;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Notifications\NewLeaveRequestNotification;
use Illuminate\Console\Command;

class TestTelegramNotificationCommand extends Command
{
    protected $signature = 'employee:test-telegram';

    protected $description = 'Test Telegram notification';

    public function handle(): int
    {
        try {
            $leaveRequest = LeaveRequest::first();

            $testNotifiable = Employee::first();
            $notification = new NewLeaveRequestNotification($leaveRequest);
            $testNotifiable->notify($notification);

            $this->info("✅ Poslano!");
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Greška: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
