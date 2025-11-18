<?php

namespace Amicus\FilamentEmployeeManagement\Observers;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\LeaveAllowance;
use Amicus\FilamentEmployeeManagement\Notifications\UserCredentialNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeObserver
{
    public function creating(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {
            try {
                $strPassword = $employee->password;
                $user = User::create([
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'email' => $employee->email,
                    'password' => Hash::make($strPassword),
                ]);

                $user->assignRole(Employee::ROLE_EMPLOYEE);

                $employee->user_id = $user->id;
                unset($employee->password);

                // Send notification after user is created and role assigned
                $user->notify(new UserCredentialNotification($strPassword));
            } catch (\Exception $e) {
                // Handle the exception - maybe log it or rethrow
                throw $e;
            }
        });
    }

    public function created(Employee $employee): void
    {
        self::onCreatedEvent($employee);
    }

    public static function onCreatedEvent(Employee $employee): void
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
    public function updating(Employee $employee): void
    {
        unset($employee->password);
    }

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
        unset($employee->password);

        // if is_active is set to false, delete the user
        if ($employee->isDirty('is_active') && ! $employee->is_active && $employee->user) {
            $employee->user->delete();
        }
        // if is_active is set to true, restore the user
        if ($employee->isDirty('is_active') && $employee->is_active && $employee->user()->withTrashed()) {
            $employee->user()->withTrashed()->restore();
        }

    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        // on delete, delete the user as well
        if ($employee->user) {
            $employee->user->delete();
        }
    }

    /**
     * Handle the Employee "restored" event.
     */
    public function restored(Employee $employee): void
    {
        // If the employee is restored, we might want to restore the user as well.
        // However, this depends on your application's logic. If you want to restore the user,
        // you can do so here. Otherwise, you can leave it empty.
        if ($employee->user()->withTrashed()) {
            $employee->user()->withTrashed()->restore();
        }
    }

    /**
     * Handle the Employee "force deleted" event.
     */
    public function forceDeleted(Employee $employee): void
    {
        //
    }
}
