<?php

namespace Amicus\FilamentEmployeeManagement\Observers;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\LeaveAllowance;
use Amicus\FilamentEmployeeManagement\Notifications\UserCredentialNotification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeObserver
{
    public function creating(Employee $employee): void
    {
        $strPassword = $employee->password;
        $user = User::create([
            'name' => $employee->first_name . ' ' . $employee->last_name,
            'email' => $employee->email,
            'password' => Hash::make($strPassword),
        ]);

        $user->notify(new UserCredentialNotification($strPassword));
        $user->assignRole(Employee::ROLE_EMPLOYEE);

        $employee->user_id = $user->id;
        unset($employee->password);
    }

    public function created(Employee $employee): void
    {
        LeaveAllowance::create([
            'employee_id' => $employee->id,
            'year' => now()->year,
            'total_days' => 20,
            'valid_until_date' => now()->addYear()->month(6)->endOfMonth(),
        ]);
    }

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
    }

    /**
     * Handle the Employee "restored" event.
     */
    public function restored(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "force deleted" event.
     */
    public function forceDeleted(Employee $employee): void
    {
        //
    }
}
