<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Actions\EmployeeAction;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\MonthlyWorkReport;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;

class MonthlySummaryWidget extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    #[On('refresh-monthly-summary')]
    public function refreshData(): void
    {
        $this->loadWorkReport();
    }

    protected string $view = 'filament-employee-management::filament.clusters.human-resources.widgets.monthly-summary-widget';

    protected int | string | array $columnSpan = 1;

    public ?Employee $record = null;

    public ?MonthlyWorkReport $workReport = null;

    public ?string $selectedMonth = null;

    public bool $showEndOfMonthAlert = false;

    public function mount(): void
    {
        $this->selectedMonth = now()->startOfMonth()->toDateString();
        $this->loadWorkReport();
    }

    protected function loadWorkReport(): void
    {
        $selectedMonth = Carbon::parse($this->selectedMonth);
        $this->workReport = MonthlyWorkReport::where('employee_id', $this->record->id)
            ->where('for_month', $selectedMonth->startOfMonth()->toDateString())
            ->first();

        $this->showEndOfMonthAlert = $this->shouldShowEndOfMonthAlert($selectedMonth);
    }

    private function shouldShowEndOfMonthAlert(Carbon $selectedMonth): bool
    {
        $now = now();
        $isCurrentMonth = $selectedMonth->isSameMonth($now);
        $isLast3Days = $now->day >= ($now->daysInMonth - 2);
        $isNotSubmitted = ! $this->workReport || ! $this->workReport->isSubmitted();

        return $isCurrentMonth && $isLast3Days && $isNotSubmitted;
    }

    public function previousMonth(): void
    {
        $current = Carbon::parse($this->selectedMonth);
        $this->selectedMonth = $current->subMonth()->startOfMonth()->toDateString();
        $this->loadWorkReport();
    }

    public function nextMonth(): void
    {
        $current = Carbon::parse($this->selectedMonth);
        $this->selectedMonth = $current->addMonth()->startOfMonth()->toDateString();
        $this->loadWorkReport();
    }

    public function canGoNext(): bool
    {
        $current = Carbon::parse($this->selectedMonth);
        $maxDate = now()->addYear()->startOfMonth();

        return $current->startOfMonth()->lt($maxDate);
    }

    public function canGoPrevious(): bool
    {
        $current = Carbon::parse($this->selectedMonth);
        $minDate = now()->subYears(2)->startOfMonth();

        return $current->startOfMonth()->gt($minDate);
    }

    public function goToCurrentMonth(): void
    {
        $this->selectedMonth = now()->startOfMonth()->toDateString();
        $this->loadWorkReport();
    }

    public function isCurrentMonth(): bool
    {
        return Carbon::parse($this->selectedMonth)->startOfMonth()->eq(now()->startOfMonth());
    }

    public function getCurrentMonthLabel(): string
    {
        $monthNames = [
            1 => 'Siječanj', 2 => 'Veljača', 3 => 'Ožujak', 4 => 'Travanj',
            5 => 'Svibanj', 6 => 'Lipanj', 7 => 'Srpanj', 8 => 'Kolovoz',
            9 => 'Rujan', 10 => 'Listopad', 11 => 'Studeni', 12 => 'Prosinac',
        ];

        $date = Carbon::parse($this->selectedMonth);

        return $monthNames[$date->month] . ' ' . $date->year;
    }

    public function summaryInfoList(Schema $schema): Schema
    {
        $selectedMonth = Carbon::parse($this->selectedMonth);
        $totals = $this->record->getMonthlyWorkReport($selectedMonth)['totals'];
        $details = [
            'Radni sati' => $totals['work_hours'],
            'Rad od kuće' => $totals['work_from_home_hours'],
            'Prekovremeno' => $totals['overtime_hours'],
            'Godišnji odmor' => $totals['vacation_hours'],
            'Bolovanje' => $totals['sick_leave_hours'],
            'Plaćeno odsustvo' => $totals['other_hours'],
            'Plaćeni neradni dani i blagdani' => $totals['holiday_hours'],
            'Predviđeno vrijeme rada' => $totals['available_hours'],
        ];

        return $schema
            ->record($this->record)
            ->state(['work_hours_details' => $details])
            ->components([
                KeyValueEntry::make('work_hours_details')
                    ->hiddenLabel()
                    ->keyLabel("Sažetak za {$selectedMonth->translatedFormat('F Y')}")
                    ->valueLabel('Broj sati'),
            ]);
    }

    public function downloadMonthlyTimeReportAction(): Action
    {
        return EmployeeAction::downloadMonthlyTimeReportAction($this->record);
    }

    public function submitForReviewAction(): Action
    {
        return Action::make('submitForReview')
            ->label('Pošalji na pregled')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Potvrda mjesečnog izvještaja')
            ->modalDescription('Jeste li sigurni da želite potvrditi radne sate za ovaj mjesec? Nakon potvrde nećete moći unositi nove sate za ovaj mjesec.')
            ->modalSubmitActionLabel('Potvrdi i pošalji')
            ->visible(fn () => $this->canSubmitForReview())
            ->action(function () {
                $selectedMonth = Carbon::parse($this->selectedMonth);
                $totals = $this->record->getMonthlyWorkReport($selectedMonth)['totals'];

                MonthlyWorkReport::submitForReview(
                    $this->record,
                    $selectedMonth,
                    $totals,
                    auth()->id()
                );

                Notification::make()
                    ->title('Izvještaj poslan na pregled')
                    ->success()
                    ->send();

                $this->loadWorkReport();
                $this->dispatch('refresh-monthly-summary');
            });
    }

    private function canSubmitForReview(): bool
    {
        if ($this->workReport && $this->workReport->isLocked()) {
            return false;
        }

        $user = auth()->user();
        $isOwnProfile = $user->employee?->id === $this->record->id;

        $settings = app(HumanResourcesSettings::class);
        $isApprover = $user->employee?->id === $settings->employee_work_hours_approver_id;
        $isAdmin = $user->isAdmin();

        return $isOwnProfile || $isApprover || $isAdmin;
    }

    public function closeMonthAction(): Action
    {
        return Action::make('closeMonth')
            ->label('Zatvori mjesec')
            ->icon(Heroicon::OutlinedLockClosed)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Zaključavanje mjeseca')
            ->modalDescription('Jeste li sigurni da želite zaključati ovaj mjesec? Izvještaj će biti finaliziran za isplatu plaće.')
            ->modalSubmitActionLabel('Zaključaj mjesec')
            ->visible(fn () => $this->canCloseMonth())
            ->action(function () {
                $selectedMonth = Carbon::parse($this->selectedMonth);
                $totals = $this->record->getMonthlyWorkReport($selectedMonth)['totals'];

                MonthlyWorkReport::approveAndLock(
                    $this->record,
                    $selectedMonth,
                    $totals
                );

                Notification::make()
                    ->title('Mjesec zaključan')
                    ->success()
                    ->send();

                $this->loadWorkReport();
                $this->dispatch('refresh-monthly-summary');
            });
    }

    private function canCloseMonth(): bool
    {
        if ($this->workReport && $this->workReport->isApproved()) {
            return false;
        }

        $user = auth()->user();
        $settings = app(HumanResourcesSettings::class);
        $isApprover = $user->employee?->id === $settings->employee_work_hours_approver_id;

        // trenutno samo approveri mogu zatvarati mjesec.
//        $isAdmin = $user->isAdmin();
//        return $isApprover || $isAdmin;
        return $isApprover;
    }

    public function returnForCorrectionAction(): Action
    {
        return Action::make('returnForCorrection')
            ->label('Vrati na ispravak')
            ->icon(Heroicon::OutlinedArrowUturnLeft)
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Vraćanje na ispravak')
            ->modalDescription('Jeste li sigurni da želite vratiti izvještaj zaposleniku na ispravak? Mjesec će biti otključan za unos sati.')
            ->modalSubmitActionLabel('Vrati na ispravak')
            ->visible(fn () => $this->canReturnForCorrection())
            ->action(function () {
                $this->workReport->returnForCorrection();

                Notification::make()
                    ->title('Izvještaj vraćen na ispravak')
                    ->success()
                    ->send();

                $this->loadWorkReport();
                $this->dispatch('refresh-monthly-summary');
            });
    }

    private function canReturnForCorrection(): bool
    {
        if (! $this->workReport || ! $this->workReport->isSubmitted() || $this->workReport->isApproved()) {
            return false;
        }

        $user = auth()->user();
        $settings = app(HumanResourcesSettings::class);
        $isApprover = $user->employee?->id === $settings->employee_work_hours_approver_id;
        $isAdmin = $user->isAdmin();

        return $isApprover || $isAdmin;
    }

    public function getApproverName(): ?string
    {
        $settings = app(HumanResourcesSettings::class);
        if ($settings->employee_work_hours_approver_id) {
            $approver = Employee::find($settings->employee_work_hours_approver_id);

            return $approver?->full_name;
        }

        return null;
    }
}
