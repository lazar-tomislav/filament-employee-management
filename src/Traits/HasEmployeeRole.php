<?php

namespace Amicus\FilamentEmployeeManagement\Traits;

use App\Models\User;

trait HasEmployeeRole
{
    const ROLE_SUPER_ADMIN = 'super_admin';

    const ROLE_EMPLOYEE = 'zaposlenik_employee';

    const ROLE_STANAR = 'stanar';

    public function isEmployee(): bool
    {
        return $this->hasRole(self::ROLE_EMPLOYEE);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    public function isStanar(): bool
    {
        return $this->hasRole(User::ROLE_STANAR);
    }

    public static function allAdministrativeUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return static::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', [
                    self::ROLE_SUPER_ADMIN,
                ]);
            })
            ->get();
    }
}
