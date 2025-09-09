<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Schemas\LeaveRequestInfolist;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Tables\LeaveRequestTable;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use App\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Pages;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationLabel = 'Zahtjevi';

    protected static ?string $modelLabel = 'Zahtjev';

    protected static ?string $pluralModelLabel = 'Zahtjevi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = TimeTracking::class;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeaveRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeaveRequestTable::configure($table);
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
            'index' => \Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Pages\ListLeaveRequests::route('/'),
            'create' => \Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Pages\CreateLeaveRequest::route('/create'),
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
