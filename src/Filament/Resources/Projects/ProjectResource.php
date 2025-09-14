<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects;

use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages\ListProjects;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages\ViewProject;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Schemas\ProjectForm;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Schemas\ProjectInfolist;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Tables\ProjectsTable;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Models\Project;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $breadcrumb="Projekti";

    protected static ?string $navigationLabel="Projekti";
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
            'view' => ViewProject::route('/{record}'),
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
