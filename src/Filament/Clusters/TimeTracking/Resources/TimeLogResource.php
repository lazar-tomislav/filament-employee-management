<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\TimeLogResource\Schemas\TimeLogForm;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\TimeLogResource\Schemas\TimeLogInfolist;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\TimeLogResource\Tables\TimeLogTable;
use Amicus\FilamentEmployeeManagement\Models\TimeLog;
use App\Filament\Clusters\TimeTracking\Resources\TimeLogResource\Pages;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TimeLogResource extends Resource
{
    protected static ?string $model = TimeLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = TimeTracking::class;

    public static function form(Schema $schema): Schema
    {
        return TimeLogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TimeLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimeLogTable::configure($table);
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
            'index' => \Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\TimeLogResource\Pages\ListTimeLogs::route('/'),
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
