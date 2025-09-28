<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects;

use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages\FinancesPage;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages\ListProjects;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages\ProjectsByStatus;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages\ProjectSchedule;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages\ViewProject;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Schemas\ProjectForm;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Schemas\ProjectInfolist;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Tables\ProjectsTable;
use Amicus\FilamentEmployeeManagement\Models\Project;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $breadcrumb = "Projekti";

    protected static ?string $navigationLabel = "5. Projekti SVE";

    protected static string|UnitEnum|null $navigationGroup = "Projekti";

    protected static ?int $navigationSort = 50;

    public static function shouldRegisterNavigation(): bool
    {
        return config('employee-management.enabled_features.projects');
    }

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProjectInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            StatusProjekta::Priprema->getSlug() => ProjectsByStatus::route('/' . StatusProjekta::Priprema->getSlug()),
            StatusProjekta::Provedba->getSlug() => ProjectsByStatus::route('/' . StatusProjekta::Provedba->getSlug()),
            StatusProjekta::Finalizacija->getSlug() => ProjectsByStatus::route('/' . StatusProjekta::Finalizacija->getSlug()),
            'view' => ViewProject::route('/{record}'),
        ];
    }

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make()
                ->label("5. Projekti SVE")
                ->url(static::getUrl('index'))
                ->isActiveWhen(fn() => request()->is('*/projects') && !request()->is('*/projects/*'))
                ->group("Projekti")
                ->sort(50),

            NavigationItem::make()
                ->label("7. Priprema projekata")
                ->url(static::getUrl(StatusProjekta::Priprema->getSlug()))
                ->isActiveWhen(fn() => request()->is('*/projects/' . StatusProjekta::Priprema->getSlug()))
                ->group("Projekti")
                ->sort(70),

            NavigationItem::make()
                ->label("8. Montaža")
                ->url(static::getUrl(StatusProjekta::Provedba->getSlug()))
                ->isActiveWhen(fn() => request()->is('*/projects/' . StatusProjekta::Provedba->getSlug()))
                ->group("Projekti")
                ->sort(80),

            NavigationItem::make()
                ->label("9. Završetak projekta")
                ->url(static::getUrl(StatusProjekta::Finalizacija->getSlug()))
                ->isActiveWhen(fn() => request()->is('*/projects/' . StatusProjekta::Finalizacija->getSlug()))
                ->group("Projekti")
                ->sort(90),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
