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
        // If a password is provided, update the user's password.
        if (! empty($employee->password) && $employee->user) {
            $employee->user->update([
                'password' => Hash::make($employee->password),
            ]);
            $employee->user->notify(new UserCredentialNotification($employee->password));
        }

        // If no user is associated, but email and password are provided, create a new user.
        if (! $employee->user && empty($employee->user_id) && ! empty($employee->password)) {
            $user = User::create([
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'email' => $employee->email,
                'password' => Hash::make($employee->password),
            ]);
            unset($employee->password);
            $employee->user_id = $user->id;
            $employee->save();

            // Send email notification
            $user->notify(new UserCredentialNotification($employee->password));
            $user->assignRole(Employee::ROLE_EMPLOYEE);
        }
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
