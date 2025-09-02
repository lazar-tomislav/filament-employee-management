<?php

namespace Amicus\FilamentEmployeeManagement\Enums;

use Filament\Support\Contracts\HasLabel;

enum LeaveRequestType: string implements HasLabel
{
    case ANNUAL_LEAVE = 'Godisnji';
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
