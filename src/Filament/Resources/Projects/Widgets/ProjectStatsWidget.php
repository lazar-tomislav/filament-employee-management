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

            Stat::make('Projekti u pripremi', Project::query()->where('status', StatusProjekta::Priprema)->count())
                ->icon(Heroicon::OutlinedCog6Tooth)
                ->color(StatusProjekta::Priprema->getColor()),

            Stat::make('Projekti u montaži', Project::query()->where('status', StatusProjekta::Provedba)->count())
                ->icon(Heroicon::OutlinedWrenchScrewdriver)
                ->color(StatusProjekta::Provedba->getColor()),

            Stat::make('Projekti u završetku', Project::query()->where('status', StatusProjekta::Finalizacija)->count())
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color(StatusProjekta::Finalizacija->getColor()),

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
