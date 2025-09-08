<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Actions\LeaveRequestAction;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveAllowanceResource\Tables\LeaveAllowanceTable;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\LeaveAllowance;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LeaveAllowanceWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public Employee $record;
    public function getColumnSpan(): int|string|array
    {
     return 1;
    }

    public function mount(): void
    {
        $this->record = Employee::find(request()->route('record'));
    }

    protected function getTableQuery(): Builder
    {
        $employeeId = $this->record->id;

        return LeaveAllowance::query()
            ->where('employee_id', $employeeId);
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return LeaveAllowanceTable::configureEmployeeView($table)
            ->query($this->getTableQuery())
            ->paginated($this->isTablePaginationEnabled())
            ->headerActions([
               LeaveRequestAction::createLeaveRequest($this->record),
            ]);
    }
}
