<?php

declare(strict_types=1);

namespace Amicus\FilamentEmployeeManagement\Policies;

use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LeaveRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LeaveRequest');
    }

    public function view(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        if (! $authUser->can('View:LeaveRequest')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $leaveRequest);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LeaveRequest');
    }

    public function update(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        if (! $authUser->can('Update:LeaveRequest')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $leaveRequest);
    }

    public function delete(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        if (! $authUser->can('Delete:LeaveRequest')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $leaveRequest);
    }

    public function restore(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        if (! $authUser->can('Restore:LeaveRequest')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $leaveRequest);
    }

    public function forceDelete(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        if (! $authUser->can('ForceDelete:LeaveRequest')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $leaveRequest);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LeaveRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LeaveRequest');
    }

    public function replicate(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        if (! $authUser->can('Replicate:LeaveRequest')) {
            return false;
        }

        return $this->canAccessRecord($authUser, $leaveRequest);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LeaveRequest');
    }

    private function canAccessRecord(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        /** @var User $authUser */
        if ($authUser->canSeeAllLeave()) {
            return true;
        }

        $ownEmployeeId = $authUser->employee?->id;

        if ($ownEmployeeId && $leaveRequest->employee_id === $ownEmployeeId) {
            return true;
        }

        return $authUser->hodDepartmentIds()->contains($leaveRequest->employee?->department_id);
    }
}
