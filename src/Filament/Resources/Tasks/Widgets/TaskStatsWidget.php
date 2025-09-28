<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Widgets;

use Amicus\FilamentEmployeeManagement\Enums\TaskStatus;
use Amicus\FilamentEmployeeManagement\Models\Task;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TaskStatsWidget extends StatsOverviewWidget
{
    protected int | array | null $columns=4;
    protected function getStats(): array
    {
        return [

            // Task stats
            Stat::make('Svi zadaci u sustavu', Task::query()->count())
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->color('info'),

            Stat::make('Zadaci za napraviti', Task::query()->where('status', TaskStatus::TODO)->count())
                ->icon(Heroicon::OutlinedClock)
                ->color(TaskStatus::TODO->getColor()),

            Stat::make('Zadaci u tijeku', Task::query()->where('status', TaskStatus::IN_PROGRESS)->count())
                ->icon(Heroicon::OutlinedPlay)
                ->color(TaskStatus::IN_PROGRESS->getColor()),

            Stat::make('Završeni zadaci', Task::query()->where('status', TaskStatus::DONE)->count())
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color(TaskStatus::DONE->getColor()),
        ];
    }
}
