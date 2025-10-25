<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Widgets;

use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
use Amicus\FilamentEmployeeManagement\Enums\TaskStatus;
use Amicus\FilamentEmployeeManagement\Models\Project;
use Amicus\FilamentEmployeeManagement\Models\Task;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProjectStatsWidget extends StatsOverviewWidget
{
    protected int | array | null $columns= 4;
    protected function getStats(): array
    {
        return [
            // Project stats
            Stat::make('Svi projekti u sustavu', Project::query()->count())
                ->icon(Heroicon::OutlinedBriefcase)
                ->color('primary'),
        ];
    }
}
