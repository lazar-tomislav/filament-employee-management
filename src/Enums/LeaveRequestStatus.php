<?php

namespace Amicus\FilamentEmployeeManagement\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LeaveRequestStatus: string implements HasColor, HasLabel
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELED = 'canceled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Na čekanju',
            self::APPROVED => 'Odobreno',
            self::REJECTED => 'Odbijeno',
            self::CANCELED => 'Otkazano',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::CANCELED => 'secondary',
        };
    }
}
