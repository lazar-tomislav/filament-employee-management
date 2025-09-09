<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestType;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Schemas\LeaveRequestForm;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\Holiday;
use Amicus\FilamentEmployeeManagement\Models\LeaveAllowance;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;

class RequestLeaveWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament-employee-management::filament.clusters.human-resources.widgets.request-leave-widget';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public ?array $data = [];

    public ?Employee $record = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return LeaveRequestForm::configure($schema, $this->record, fn() => $this->recalculateDays())->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        if(str_starts_with($data['leave_type_option'], 'allowance_')){
            $type = LeaveRequestType::ANNUAL_LEAVE;
            $allowanceId = (int)str_replace('allowance_', '', $data['leave_type_option']);
        }else{
            $type = LeaveRequestType::from($data['leave_type_option']);
        }

        $employeeId = $this->record->id ?? $data['employee_id'];
        $employee = Employee::find($employeeId);

        if(!$employee){
            Notification::make()->title('Greška')->body('Zaposlenik nije pronađen.')->danger()->send();
            return;
        }
        $requestData = [
            'employee_id' => $employee->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'notes' => $data['notes'],
            'days_count' => $this->bookingDays,
            'status' => 'pending',
            'type' => $type,
        ];

        if($type === LeaveRequestType::ANNUAL_LEAVE){
            $allowance = LeaveAllowance::find($allowanceId);

            if(!$allowance || $allowance->employee_id !== $employee->id){
                Notification::make()->title('Greška: ' . $employee->first_name)->body('Odabrani godišnji odmor nije ispravan za zaposlenika.')->danger()->send();
                report(new \Exception("Invalid leave allowance ID: {$allowanceId} for employee ID: {$employee->id}"));
                return;
            }

            if($this->bookingDays > $allowance->available_days){
                Notification::make()->title('Greška: ' . $employee->first_name)->body('Zaposlenik nema dovoljno slobodnih dana.')->danger()->send();
                report(new \Exception("Not enough available days for employee ID: {$employee->id}"));
                return;
            }
            $requestData['leave_allowance_id'] = $allowance->id;
        }

        LeaveRequest::create($requestData);

        Notification::make()->title('Zahtjev poslan')->success()->send();

        $this->form->fill();

        $this->dispatch("leave-request-created");
    }

    public function getBookingDaysProperty(): int
    {
        $startDate = Carbon::parse($this->data['start_date'] ?? null);
        $endDate = Carbon::parse($this->data['end_date'] ?? null);

        if(!$startDate?->isValid() || !$endDate?->isValid() || $endDate->isBefore($startDate)){
            return 0;
        }

        $holidays = Holiday::getHolidayDatesInRange($startDate, $endDate);
        $bookingDays = 0;

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            if (in_array($date->dayOfWeek, Employee::WORK_DAYS) && !in_array($date->format('Y-m-d'), $holidays)) {
                $bookingDays++;
            }
        }

        return $bookingDays;
    }

    public function getDateRangeProperty(): string
    {
        $startDate = Carbon::parse($this->data['start_date'] ?? null);
        $endDate = Carbon::parse($this->data['end_date'] ?? null);
        if(!$startDate->isValid() || !$endDate->isValid() || $endDate->isBefore($startDate)){
            return '';
        }

        return $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y');
    }

    public function getDaysAvailableAfterReservationProperty(): string
    {
        $leaveAllowance = $this->getLeaveAllowance();
        return $leaveAllowance ? ($leaveAllowance->available_days - $this->bookingDays) : "&#8734;";
    }

    public function getCurrentlyAvailableAllowanceDaysProperty(): string
    {
        $leaveAllowance = $this->getLeaveAllowance();
        return $leaveAllowance ? $leaveAllowance->available_days : "&#8734;";
    }

    private function getLeaveAllowance(): ?LeaveAllowance
    {
        if(!isset($this->data['leave_type_option']) || !str_starts_with($this->data['leave_type_option'], 'allowance_')){
            return null;
        }
        $leaveAllowanceId = (int)str_replace('allowance_', '', $this->data['leave_type_option']);
        return LeaveAllowance::find($leaveAllowanceId);
    }

    public function recalculateDays(): void
    {
        // This method is a trigger for re-rendering.
    }
}
