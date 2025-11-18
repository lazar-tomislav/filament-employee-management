<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\MonthlyWorkReport;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class MonthlySummaryWidget extends Widget implements HasSchemas, HasActions
{
    use InteractsWithSchemas;
    use InteractsWithActions;

    protected string $view = 'filament-employee-management::filament.clusters.human-resources.widgets.monthly-summary-widget';

    protected int|string|array $columnSpan = 1;

    public ?Employee $record = null;

    public ?MonthlyWorkReport $workReport = null;

    public ?array $data = [];

    public function mount(): void
    {
        $this->data['showForMonth'] = now()->startOfMonth()->toDateString();
        $this->form->fill($this->data);
        $this->loadWorkReport();
    }

    public function updatedData($value, $key): void
    {
        if($key === 'showForMonth'){
            $this->loadWorkReport();
        }
    }

    protected function loadWorkReport(): void
    {
        $selectedMonth = Carbon::parse($this->data['showForMonth']);
        $this->workReport = MonthlyWorkReport::where('employee_id', $this->record->id)
            ->where('for_month', $selectedMonth->startOfMonth()->toDateString())
            ->first();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('showForMonth')
                    ->label('Odaberite mjesec')
                    ->hiddenLabel()
                    ->options($this->getMonthOptions())
                    ->live(),
            ])->statePath('data');
    }

    // You can remove this method and use the 'updatedShowForMonth' method below.
    private function getMonthOptions(): array
    {
        $options = [];
        $start = $this->record->created_at->startOfMonth();
        $end = now()->startOfMonth();

        $current = $end->copy();
        while($current->gte($start)){
            $options[$current->toDateString()] = $current->format('m/Y');
            $current->subMonth();
        }
        return $options;
    }

    public function summaryInfoList(Schema $schema): Schema
    {
        $selectedMonth = Carbon::parse($this->data['showForMonth']);
        $totals = $this->record->getMonthlyWorkReport($selectedMonth)['totals'];

        $details = [
            "Radni sati" => $totals['work_hours'],
            "Prekovremeno" => $totals['overtime_hours'],
            "Godišnji odmor" => $totals['vacation_hours'],
            "Bolovanje" => $totals['sick_leave_hours'],
            "Plaćeno odsustvo" => $totals['other_hours'],
            "Prevdiđeno vrijeme rada" => $totals['available_hours'],
        ];


        return $schema
            ->record($this->record)
            ->state(['work_hours_details' => $details])
            ->components([
                KeyValueEntry::make('work_hours_details')
                    ->hiddenLabel()
                    ->keyLabel("Sažetak za {$selectedMonth->translatedFormat('F Y')}")
                    ->valueLabel('Broj sati')
                    ->belowContent([
                        Action::make('Odbij')
                            ->icon(Heroicon::OutlinedXCircle)
                            ->color('danger')
                            ->button()
                            ->requiresConfirmation()
                            ->schema([
                                Textarea::make('deny_reason')
                                    ->label('Razlog odbijanja')
                                    ->required(),
                            ])
                            ->action(function (array $data) use ($selectedMonth, $totals) {
                                try{
                                    MonthlyWorkReport::updateReportStatus($this->record, $selectedMonth, $totals, false, $data['deny_reason']);
                                    Notification::make()->title('Izvještaj odbijen')->success()->send();
                                    $this->loadWorkReport();
                                }catch(\Exception $exception){
                                    report($exception);
                                    Notification::make()
                                        ->title('Greška')
                                        ->body('Došlo je do greške prilikom odbijanja izvještaja. Molimo pokušajte ponovno.')
                                        ->danger()
                                        ->send();
                                }
                            }),

                        Action::make('Odobri')
                            ->icon(Heroicon::OutlinedCheck)
                            ->color('success')
                            ->button()
                            ->requiresConfirmation()
                            ->action(function () use ($selectedMonth, $totals) {
                                MonthlyWorkReport::updateReportStatus($this->record, $selectedMonth, $totals, true);
                                Notification::make()->title('Izvještaj odobren')->success()->send();
                                $this->loadWorkReport();
                            }),
                    ])
            ]);
    }
}
