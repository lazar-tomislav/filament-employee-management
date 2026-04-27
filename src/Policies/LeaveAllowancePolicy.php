<?php

declare(strict_types=1);

namespace Amicus\FilamentEmployeeManagement\Policies;

use Amicus\FilamentEmployeeManagement\Models\LeaveAllowance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LeaveAllowancePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LeaveAllowance');
    }

    public function view(AuthUser $authUser, LeaveAllowance $leaveAllowance): bool
    {
        if (! $authUser->can('View:LeaveAllowance')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $leaveAllowance);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LeaveAllowance');
    }

    public function update(AuthUser $authUser, LeaveAllowance $leaveAllowance): bool
    {
        if (! $authUser->can('Update:LeaveAllowance')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $leaveAllowance);
    }

    public function delete(AuthUser $authUser, LeaveAllowance $leaveAllowance): bool
    {
        if (! $authUser->can('Delete:LeaveAllowance')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $leaveAllowance);
    }

    public function restore(AuthUser $authUser, LeaveAllowance $leaveAllowance): bool
    {
        if (! $authUser->can('Restore:LeaveAllowance')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $leaveAllowance);
    }

    public function forceDelete(AuthUser $authUser, LeaveAllowance $leaveAllowance): bool
    {
        if (! $authUser->can('ForceDelete:LeaveAllowance')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $leaveAllowance);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LeaveAllowance');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LeaveAllowance');
    }

    public function replicate(AuthUser $authUser, LeaveAllowance $leaveAllowance): bool
    {
        if (! $authUser->can('Replicate:LeaveAllowance')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $leaveAllowance);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LeaveAllowance');
    }

    private function canAccessRecord(AuthUser $authUser, LeaveAllowance $leaveAllowance): bool
    {
        /** @var User $authUser */
        if ($authUser->canSeeAllLeave()) {
            return true;
        }

        $ownEmployeeId = $authUser->employee?->id;

        if ($ownEmployeeId && $leaveAllowance->employee_id === $ownEmployeeId) {
            return true;
        }

        return $authUser->hodDepartmentIds()->contains($leaveAllowance->employee?->department_id);
    }
}
