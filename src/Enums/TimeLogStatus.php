<?php

namespace Amicus\FilamentEmployeeManagement\Enums;

enum TimeLogStatus: string
{
    case PLANNED = 'planned';
    case CONFIRMED = 'confirmed';

    public function getLabel(): string
    {
        return match ($this) {
            self::PLANNED => 'Planirano',
            self::CONFIRMED => 'Potvrđeno',
        };
    }

    public static function default(): self
    {
        return self::CONFIRMED;
    }
}