<?php

namespace Amicus\FilamentEmployeeManagement\Observers;

use Amicus\FilamentEmployeeManagement\Models\LeaveAllowance;
use Illuminate\Validation\ValidationException;

class LeaveAllowanceObserver
{
    /**
     * Handle the LeaveAllowance "creating" event.
     */
    public function creating(LeaveAllowance $leaveAllowance): void
    {
        $this->checkForDuplicate($leaveAllowance);
    }

    /**
     * Handle the LeaveAllowance "updating" event.
     */
    public function updating(LeaveAllowance $leaveAllowance): void
    {
        if ($leaveAllowance->isDirty('employee_id') || $leaveAllowance->isDirty('year')) {
            $this->checkForDuplicate($leaveAllowance);
        }
    }

    /**
     * Check for duplicate LeaveAllowance for the same employee and year.
     *
     * @throws ValidationException
     */
    protected function checkForDuplicate(LeaveAllowance $leaveAllowance): void
    {
        $query = LeaveAllowance::where('employee_id', $leaveAllowance->employee_id)
            ->where('year', $leaveAllowance->year);

        if ($leaveAllowance->exists) {
            $query->where('id', '!=', $leaveAllowance->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'year' => 'Pravo na godišnji odmor za ovog zaposlenika i ovu godinu već postoji.',
            ]);
        }
    }
}
