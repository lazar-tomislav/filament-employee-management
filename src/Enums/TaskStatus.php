<?php

namespace Amicus\FilamentEmployeeManagement\Enums;

use Filament\Support\Contracts\HasLabel;

enum TaskStatus: string implements HasLabel
{
    case TODO = 'TODO';
    case IN_PROGRESS = 'IN_PROGRESS';
    case DONE = 'DONE';
    case POSTPONED = 'POSTPONED';

    public function getLabel(): string
    {
        return match ($this) {
            self::TODO => 'Treba napraviti',
            self::IN_PROGRESS => 'U tijeku',
            self::DONE => 'Riješeno',
            self::POSTPONED => 'Odgođeno',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::TODO => 'gray',
            self::IN_PROGRESS => 'warning',
            self::DONE => 'success',
            self::POSTPONED => 'info',
        };
    }
}
