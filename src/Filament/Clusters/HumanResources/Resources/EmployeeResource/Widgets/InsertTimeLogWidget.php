<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets;

use Amicus\FilamentEmployeeManagement\Enums\LogType;
use Amicus\FilamentEmployeeManagement\Enums\TimeLogStatus;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\TimeLogResource\Schemas\TimeLogForm;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\Holiday;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Models\TimeLog;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Livewire\Attributes\Url;

class InsertTimeLogWidget extends Widget implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected string $view = 'filament-employee-management::filament.clusters.human-resources.widgets.insert-time-log-widget';


    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public ?array $data = [];

    public ?array $weekData = [];

    #[Url]
    public ?string $selectedDate = null;

    public ?Employee $record = null;

    public function mount(): void
    {
        if(!$this->selectedDate){
            $this->selectedDate = now()->format('Y-m-d');
        }

        $this->form->fill([
            'employee_id' => $this->record?->id,
            'date' => now()->format('Y-m-d'),
            "hours" => 8,
        ]);

        $this->weekForm->fill([
            'selected_date' => $this->selectedDate,
        ]);
    }

    public function weekForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\DatePicker::make('selected_date')
                    ->hiddenLabel()
                    ->reactive()
                    ->native(false)
                    ->closeOnDateSelection()
                    ->displayFormat('d.m.Y')
                    ->default(now())
                    ->format("Y-m-d")
                    ->afterStateUpdated(function ($state) {
                        $carbonDate = Carbon::parse($state);
                        $this->selectDate($carbonDate->format('Y-m-d'));
                    }),
            ])
            ->statePath('weekData');
    }

    public function form(Schema $schema): Schema
    {
        return TimeLogForm::configureForEmployeeView($schema)
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $employeeId = $this->record?->id ?? $data['employee_id'];
        $employee = Employee::find($employeeId);

        if(!$employee){
            Notification::make()
                ->title('Greška')
                ->body('Zaposlenik nije pronađen.')
                ->danger()
                ->send();
            return;
        }

        try{
            TimeLog::create([
                'employee_id' => $employee->id,
                'date' => $data['date'] ?? $this->selectedDate,
                'hours' => $data['hours'],
                'description' => $data['description'],
                'status' => $data['status'] ?? TimeLogStatus::default(),
                'log_type' => $data['log_type'] ?? LogType::RADNI_SATI,
            ]);

            Notification::make()
                ->title('Uspješno dodano')
                ->body('Radni sati su uspješno uneseni.')
                ->success()
                ->send();

            $this->form->fill([
                'employee_id' => $this->record?->id,
                'date' => now()->format('Y-m-d'),
                'hours' => 8,
                'description' => '',
            ]);

            $this->dispatch('time-log-created');
        }catch(\Exception $e){
            Notification::make()
                ->title('Greška')
                ->body('Dogodila se greška prilikom unosa: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getWeekDays(): array
    {
        if(!$this->selectedDate){
            $this->selectedDate = now()->format('Y-m-d');
        }

        $date = Carbon::parse($this->selectedDate);
        $startOfWeek = $date->copy()->startOfWeek();

        $days = [];

        for($i = 0; $i < 7; $i++){
            $currentDay = $startOfWeek->copy()->addDays($i);

            $totalHours = TimeLog::getTotalHoursForDate($this->record?->id, $currentDay->format('Y-m-d'));

            $days[] = [
                'date' => $currentDay->format('Y-m-d'),
                'day_name' => $currentDay->locale('hr')->format('D'),
                'day_number' => $currentDay->format('j'),
                'hours' => $totalHours,
                'is_today' => $currentDay->isToday(),
                'is_weekend' => $currentDay->isWeekend(),
                'is_selected' => $currentDay->format('Y-m-d') === $this->selectedDate,
            ];
        }

        return $days;
    }

    public function getTotalWeekHours(): string
    {
        if(!$this->selectedDate){
            $this->selectedDate = now()->format('Y-m-d');
        }

        $date = Carbon::parse($this->selectedDate);
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        return TimeLog::getTotalHoursForWeek(
            $this->record?->id,
            $startOfWeek->format('Y-m-d'),
            $endOfWeek->format('Y-m-d')
        );
    }

    public function selectDate($date): void
    {
        $this->selectedDate = $date;

        // Ažuriraj i formu za unos sati da koristi odabrani datum
        $this->form->fill([
            'employee_id' => $this->record?->id,
            'date' => $this->selectedDate,
            'hours' => 8,
            'description' => '',
        ]);

        // Ažuriraj i weekForm
        $this->weekForm->fill([
            'selected_date' => $this->selectedDate,
        ]);
    }

    public function getTimeLogsForSelectedDate()
    {
        if(!$this->selectedDate){
            $this->selectedDate = now()->format('Y-m-d');
        }

        $employeeId = $this->record?->id;

        if(!$employeeId){
            return collect();
        }

        $timeLogs = TimeLog::getTimeLogsForDate($employeeId, $this->selectedDate);
        $holidays = Holiday::getHolidaysForDate(Carbon::parse($this->selectedDate));
        $leaveRequests = LeaveRequest::getLeaveRequestsForDate($employeeId, $this->selectedDate);

        $mappedTimeLogs = $timeLogs->map(function ($timeLog) {
            return [
                'id' => $timeLog->id,
                "color" => 'bg-green-700/50',
                "name" => "Redovan unos sati",
                'description' => $timeLog->description,
                'hours' => $timeLog->formatted_hours,
                "can_delete" => true,
                "can_edit" => true,
            ];
        });

        $mappedHolidays = $holidays->map(function ($holiday) {
            return [
                'id' => 'holiday-' . $holiday->id,
                "name" => "Praznik - Neradni dan",
                "color" => 'bg-blue-700/50',
                'description' => $holiday->name,
                'hours' => 8,
                "can_delete" => false,
                "can_edit" => false,
            ];
        });

        $mappedLeaveRequests = $leaveRequests->map(function ($leaveRequest) {
            return [
                'id' => 'leave-' . $leaveRequest->id,
                "name" => "Odustnost",
                "color" => 'bg-red-700/50',
                'description' => $leaveRequest->type->getLabel(),
                'hours' => 8,
                "can_delete" => false,
                "can_edit" => false,
            ];
        });

        return $mappedTimeLogs->concat($mappedHolidays)->concat($mappedLeaveRequests);
    }

    public function editAction(): Action
    {
        return Action::make('edit')
            ->label('Uredi')
            ->icon('heroicon-m-pencil-square')
            ->color('warning')
            ->mountUsing(function (Schema $schema, array $arguments) {
                if ($timeLog = TimeLog::find($arguments['timeLog'] ?? null)) {
                    $schema->fill($timeLog->only(['hours', 'description']));
                }
            })
            ->schema(fn($schema) => TimeLogForm::configureForEmployeeView($schema)
                ->columns(1)
            )
            ->action(function (array $arguments, $data) {
                try{
                    $timeLog = $arguments['timeLog'] ?? null;
                    $timeLog = TimeLog::find($timeLog);
                    if(!$data['hours'] && !$data['description']){
                        Notification::make()->title('Greška')->body('Morate unijeti barem jedan podatak za uređivanje.')->danger()->send();
                        return;
                    }
                    $timeLog->update([
                        'hours' => $data['hours'],
                        'description' => $data['description'],
                    ]);
                    Notification::make()->title('Izmjene su uspješno spremljene.')->success()->send();
                }catch(\Exception $e){
                    report($e);
                    Notification::make()->title('Greška')->body('Dogodila se greška prilikom uređivanja')->danger()->send();
                }
            });
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label('Obriši')
            ->icon('heroicon-m-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                try{
                    $timeLog = $arguments['timeLog'] ?? null;
                    $timeLog = TimeLog::find($timeLog);
                    $timeLog->delete();
                    Notification::make()
                        ->title('Uspješno obrisano')
                        ->body('Radni sati su uspješno obrisani.')
                        ->success()
                        ->send();
                }catch(\Exception $e){
                    Notification::make()
                        ->title('Greška')
                        ->body('Dogodila se greška prilikom brisanja: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
