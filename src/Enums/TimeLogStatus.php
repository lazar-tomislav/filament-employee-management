<?php

namespace Amicus\FilamentEmployeeManagement\Enums;

use Filament\Support\Contracts\HasLabel;

enum TimeLogStatus: string implements HasLabel
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
