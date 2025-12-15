<?php

namespace Amicus\FilamentEmployeeManagement\Enums;

use Filament\Support\Contracts\HasLabel;

enum PhoneNumberType: string implements HasLabel
{
    case PRIVATE = 'private';
    case BUSINESS = 'business';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PRIVATE => 'Privatni',
            self::BUSINESS => 'Poslovni',
        };
    }
}
