<?php

declare(strict_types=1);

namespace Amicus\FilamentEmployeeManagement\Policies;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EmployeePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Employee');
    }

    public function view(AuthUser $authUser, Employee $employee): bool
    {
        if (! $authUser->can('View:Employee')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $employee);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Employee');
    }

    public function update(AuthUser $authUser, Employee $employee): bool
    {
        if (! $authUser->can('Update:Employee')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $employee);
    }

    public function delete(AuthUser $authUser, Employee $employee): bool
    {
        if (! $authUser->can('Delete:Employee')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $employee);
    }

    public function restore(AuthUser $authUser, Employee $employee): bool
    {
        if (! $authUser->can('Restore:Employee')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $employee);
    }

    public function forceDelete(AuthUser $authUser, Employee $employee): bool
    {
        if (! $authUser->can('ForceDelete:Employee')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $employee);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Employee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Employee');
    }

    public function replicate(AuthUser $authUser, Employee $employee): bool
    {
        if (! $authUser->can('Replicate:Employee')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $employee);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Employee');
    }

    private function canAccessRecord(AuthUser $authUser, Employee $employee): bool
    {
        /** @var User $authUser */
        if ($authUser->canSeeAllLeave()) {
            return true;
        }

        $ownEmployeeId = $authUser->employee?->id;

        if ($ownEmployeeId && $employee->id === $ownEmployeeId) {
            return true;
        }

        return $authUser->hodDepartmentIds()->contains($employee->department_id);
    }
}
