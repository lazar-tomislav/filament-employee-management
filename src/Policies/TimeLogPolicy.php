<?php

declare(strict_types=1);

namespace Amicus\FilamentEmployeeManagement\Policies;

use Amicus\FilamentEmployeeManagement\Models\TimeLog;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TimeLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TimeLog');
    }

    public function view(AuthUser $authUser, TimeLog $timeLog): bool
    {
        return $authUser->can('View:TimeLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TimeLog');
    }

    public function update(AuthUser $authUser, TimeLog $timeLog): bool
    {
        return $authUser->can('Update:TimeLog');
    }

    public function delete(AuthUser $authUser, TimeLog $timeLog): bool
    {
        return $authUser->can('Delete:TimeLog');
    }

    public function restore(AuthUser $authUser, TimeLog $timeLog): bool
    {
        return $authUser->can('Restore:TimeLog');
    }

    public function forceDelete(AuthUser $authUser, TimeLog $timeLog): bool
    {
        return $authUser->can('ForceDelete:TimeLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TimeLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TimeLog');
    }

    public function replicate(AuthUser $authUser, TimeLog $timeLog): bool
    {
        return $authUser->can('Replicate:TimeLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TimeLog');
    }

}
