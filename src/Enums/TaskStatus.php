<?php

namespace Amicus\FilamentEmployeeManagement\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TaskStatus: string implements HasLabel, HasColor
{
    case TODO = 'TODO';
    case IN_PROGRESS = 'IN_PROGRESS';
    case DONE = 'DONE';
    case POSTPONED = 'POSTPONED';

    public function getLabel(): string
    {
        return match ($this) {
            self::TODO => 'To Do',
            self::IN_PROGRESS => 'In Progress',
            self::DONE => 'Done',
            self::POSTPONED => 'On Hold',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::TODO => 'gray',
            self::IN_PROGRESS => 'warning',
            self::DONE => 'success',
            self::POSTPONED => 'info',
        };
    }

    public function getColorClass(): string
    {
        return match ($this) {
            self::TODO => 'task-status-todo',
            self::IN_PROGRESS => 'task-status-in-progress',
            self::DONE => 'task-status-done',
            self::POSTPONED => 'task-status-postponed',
        };
    }
}
