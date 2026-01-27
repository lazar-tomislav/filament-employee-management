<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets;

use Amicus\FilamentEmployeeManagement\Exports\EmployeeReportExport;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Actions\EmployeeAction;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas\EmployeeForm;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\MonthlyWorkReport;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;

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
                //                    ->belowContent([
                //                        Action::make('Odbij')
                //                            ->icon(Heroicon::OutlinedXCircle)
                //                            ->color('danger')
                //                            ->button()
                //                            ->requiresConfirmation()
                //                            ->schema([
                //                                Textarea::make('deny_reason')
                //                                    ->label('Razlog odbijanja')
                //                                    ->required(),
                //                            ])
                //                            ->action(function (array $data) use ($selectedMonth, $totals) {
                //                                try{
                //                                    MonthlyWorkReport::updateReportStatus($this->record, $selectedMonth, $totals, false, $data['deny_reason']);
                //                                    Notification::make()->title('Izvještaj odbijen')->success()->send();
                //                                    $this->loadWorkReport();
                //                                }catch(\Exception $exception){
                //                                    report($exception);
                //                                    Notification::make()
                //                                        ->title('Greška')
                //                                        ->body('Došlo je do greške prilikom odbijanja izvještaja. Molimo pokušajte ponovno.')
                //                                        ->danger()
                //                                        ->send();
                //                                }
                //                            }),
                //
                //                        Action::make('Odobri')
                //                            ->icon(Heroicon::OutlinedCheck)
                //                            ->color('success')
                //                            ->button()
                //                            ->requiresConfirmation()
                //                            ->action(function () use ($selectedMonth, $totals) {
                //                                MonthlyWorkReport::updateReportStatus($this->record, $selectedMonth, $totals, true);
                //                                Notification::make()->title('Izvještaj odobren')->success()->send();
                //                                $this->loadWorkReport();
                //                            }),
                //                    ])
            ]);
    }

    public function downloadMonthlyTimeReportAction(): Action
    {
        return EmployeeAction::downloadMonthlyTimeReportAction($this->record);
    }
}
