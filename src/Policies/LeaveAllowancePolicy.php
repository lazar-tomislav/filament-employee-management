<?php

declare(strict_types=1);

namespace Amicus\FilamentEmployeeManagement\Policies;

use Amicus\FilamentEmployeeManagement\Models\LeaveAllowance;
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
        return $authUser->can('View:LeaveAllowance');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LeaveAllowance');
    }

    public function update(AuthUser $authUser, LeaveAllowance $leaveAllowance): bool
    {
        return $authUser->can('Update:LeaveAllowance');
    }

    public function delete(AuthUser $authUser, LeaveAllowance $leaveAllowance): bool
    {
        return $authUser->can('Delete:LeaveAllowance');
    }

    public function restore(AuthUser $authUser, LeaveAllowance $leaveAllowance): bool
    {
        return $authUser->can('Restore:LeaveAllowance');
    }

    public function forceDelete(AuthUser $authUser, LeaveAllowance $leaveAllowance): bool
    {
        return $authUser->can('ForceDelete:LeaveAllowance');
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
        return $authUser->can('Replicate:LeaveAllowance');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LeaveAllowance');
    }

}
