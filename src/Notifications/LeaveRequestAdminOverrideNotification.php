<?php

declare(strict_types=1);

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Mail\LeaveRequestAdminOverrideMail;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Middleware\RateLimited;

class LeaveRequestAdminOverrideNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 10;

    public int $maxExceptions = 3;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public string $reason,
        public string $statusKey,
    ) {}

    /**
     * @return array<int, object>
     */
    public function middleware(object $notifiable, string $channel): array
    {
        return match ($channel) {
            'mail' => [(new RateLimited('resend-api'))->releaseAfter(3)],
            default => [],
        };
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): LeaveRequestAdminOverrideMail
    {
        return new LeaveRequestAdminOverrideMail($this->leaveRequest, $this->reason, $this->statusKey);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $startDate = $this->leaveRequest->start_date->format('d.m.Y');
        $endDate = $this->leaveRequest->end_date->format('d.m.Y');
        $approverName = $this->resolveWorkHoursApproverName();
        $contactLine = $approverName
            ? "Za pitanja se obratite osobi: {$approverName} (voditelj za radne sate)."
            : 'Za pitanja se obratite voditelju za radne sate.';

        return FilamentNotification::make()
            ->title('Promjena statusa zahtjeva za godišnji odmor')
            ->body("Vaš odobreni zahtjev za godišnji odmor ({$startDate} – {$endDate}) administrator je stornirao. Razlog: {$this->reason}. {$contactLine}")
            ->getDatabaseMessage();
    }

    private function resolveWorkHoursApproverName(): ?string
    {
        $approverId = app(HumanResourcesSettings::class)->employee_work_hours_approver_id;

        if (! $approverId) {
            return null;
        }

        return Employee::find($approverId)?->full_name;
    }
}
