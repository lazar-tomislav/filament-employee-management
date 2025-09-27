<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks;

use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Pages\ListTasks;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Tables\TasksTable;
use Amicus\FilamentEmployeeManagement\Models\Task;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = "Zadaci";
    protected static ?string $breadcrumb = "Zadaci";
    protected static ?int $navigationSort = 3000;

    public static function shouldRegisterNavigation(): bool
    {
        return config('employee-management.enabled_features.projects');
    }

    public static function table(Table $table): Table
    {
        return TasksTable::configure($table);
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
            'index' => ListTasks::route('/'),
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
