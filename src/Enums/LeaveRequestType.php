<?php

namespace Amicus\FilamentEmployeeManagement\Enums;

use Filament\Support\Contracts\HasLabel;

enum LeaveRequestType: string implements HasLabel
{
    case ANNUAL_LEAVE = 'godisnji';
    case SICK_LEAVE = 'bolovanje';
    case PAID_LEAVE = 'placeni_slobodan_dan';
    case MATERNITY_LEAVE = 'porodiljni';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ANNUAL_LEAVE => 'Godišnji odmor',
            self::SICK_LEAVE => 'Bolovanje',
            self::PAID_LEAVE => 'Plaćeni slobodan dan',
            self::MATERNITY_LEAVE => 'Porodiljni',
        };
    }

    public function isAutoApproved(): bool
    {
        return in_array($this, [self::SICK_LEAVE, self::MATERNITY_LEAVE], true);
    }
}
