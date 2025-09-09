<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveAllowanceResource\Schemas\LeaveAllowanceForm;
use Amicus\FilamentEmployeeManagement\Models\LeaveAllowance;
use App\Filament\Clusters\HumanResources\Resources\LeaveAllowanceResource\Pages;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeaveAllowanceResource extends Resource
{
    protected static ?string $model = LeaveAllowance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = "G.O zaposlenika";
    protected static ?int $navigationSort=50;
    protected static ?string $cluster = HumanResources::class;

    public static function form(Schema $schema): Schema
    {
        return LeaveAllowanceForm::configure($schema,$schema->getRecord()?->employee()->first());
    }

    public static function table(Table $table): Table
    {
        return HumanResources\Resources\LeaveAllowanceResource\Tables\LeaveAllowanceTable::configure($table);
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
            'index' => HumanResources\Resources\LeaveAllowanceResource\Pages\ListLeaveAllowances::route('/'),
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
