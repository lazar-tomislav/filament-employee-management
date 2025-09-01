<?php

namespace Packages\FilamentEmployeeManagement\Enums;

use Filament\Support\Contracts\HasLabel;

enum LeaveRequestType: string implements HasLabel
{
    case ANNUAL_LEAVE = 'godisnji';
    case SICK_LEAVE = 'bolovanje';
    case PAID_LEAVE = 'placeni_slobodan_dan';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ANNUAL_LEAVE => 'Godišnji odmor',
            self::SICK_LEAVE => 'Bolovanje',
            self::PAID_LEAVE => 'Plaćeni slobodan dan',
        };
    }
}
