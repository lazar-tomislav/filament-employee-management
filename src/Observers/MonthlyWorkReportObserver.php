<?php

namespace Amicus\FilamentEmployeeManagement\Observers;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\MonthlyWorkReport;
use Amicus\FilamentEmployeeManagement\Notifications\MonthlyWorkReportApprovedNotification;
use Amicus\FilamentEmployeeManagement\Notifications\MonthlyWorkReportResponseNotification;
use Amicus\FilamentEmployeeManagement\Notifications\MonthlyWorkReportReturnedNotification;
use Amicus\FilamentEmployeeManagement\Notifications\MonthlyWorkReportSubmittedNotification;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use App\Models\User;

class MonthlyWorkReportObserver
{
    public function created(MonthlyWorkReport $monthlyWorkReport): void
    {
        $this->handleSubmission($monthlyWorkReport);
        $this->handleApproval($monthlyWorkReport);
    }

    public function updated(MonthlyWorkReport $monthlyWorkReport): void
    {
        $this->handleSubmission($monthlyWorkReport);
        $this->handleApproval($monthlyWorkReport);
        $this->handleReturnForCorrection($monthlyWorkReport);
        $this->handleDenial($monthlyWorkReport);
    }

    private function handleSubmission(MonthlyWorkReport $report): void
    {
        if ($report->isDirty('submitted_at') && $report->submitted_at !== null && $report->approved_at === null) {
            $approver = $this->getApprover();
            if ($approver?->user) {
                $approver->user->notify(new MonthlyWorkReportSubmittedNotification($report));
            }
        }
    }

    private function handleApproval(MonthlyWorkReport $report): void
    {
        if ($report->isDirty('approved_at') && $report->approved_at !== null) {
            $report->employee?->user?->notify(new MonthlyWorkReportApprovedNotification($report));
        }
    }

    private function handleReturnForCorrection(MonthlyWorkReport $report): void
    {
        if ($report->isDirty('submitted_at') && $report->submitted_at === null && $report->getOriginal('submitted_at') !== null) {
            $report->employee?->user?->notify(new MonthlyWorkReportReturnedNotification($report));
        }
    }

    private function handleDenial(MonthlyWorkReport $report): void
    {
        if ($report->isDirty('denied_at') && $report->denied_at !== null) {
            if (! empty($report->deny_reason)) {
                User::allAdministrativeUsers()->each(function (User $user) use ($report) {
                    $user->notify(new MonthlyWorkReportResponseNotification($report));
                });
            }
        }
    }

    private function getApprover(): ?Employee
    {
        $settings = app(HumanResourcesSettings::class);
        if ($settings->employee_work_hours_approver_id) {
            return Employee::find($settings->employee_work_hours_approver_id);
        }

        return null;
    }
}
