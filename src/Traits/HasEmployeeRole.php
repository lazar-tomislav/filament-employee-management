<?php

namespace Amicus\FilamentEmployeeManagement\Traits;
trait HasEmployeeRole
{

    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_UPRAVA_ADMIN = 'uprava_admin';
    const ROLE_EMPLOYEE = 'zaposlenik_employee';
    const ROLE_URED_ADMINISTRATIVNO_OSOBLJE = 'ured_administrativno_osoblje';

    /**
     * Check if the user has the employee role.
     *
     * @return bool
     */
    public function isEmployee(): bool
    {
        return $this->hasRole(self::ROLE_EMPLOYEE);
    }

    public function isAdmin(bool $strict = false): bool
    {
        if($strict){
            return $this->hasRole(self::ROLE_UPRAVA_ADMIN);
        }
        return $this->hasRole(self::ROLE_UPRAVA_ADMIN) || $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN) || $this->hasRole(self::ROLE_UPRAVA_ADMIN);
    }

    public function isUredAdministrativnoOsoblje(): bool
    {
        // administrativno + roles above it can see
        return $this->hasRole(self::ROLE_URED_ADMINISTRATIVNO_OSOBLJE) || $this->isSuperAdmin() || $this->isAdmin();
    }

    public static function allAdministrativeUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return static::query()
            ->where(function ($query) {
                $query->where('role', self::ROLE_UPRAVA_ADMIN)
                    ->orWhere('role', self::ROLE_SUPER_ADMIN)
                    ->orWhere('role', self::ROLE_URED_ADMINISTRATIVNO_OSOBLJE);
            })->get();

    }
}
