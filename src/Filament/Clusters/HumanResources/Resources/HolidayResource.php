<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\HolidayResource\Schemas\HolidayForm;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\HolidayResource\Tables\HolidayTable;
use App\Filament\Clusters\HumanResources\Resources\HolidayResource\Pages;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class HolidayResource extends Resource
{
    protected static ?string $model = \Amicus\FilamentEmployeeManagement\Models\Holiday::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string | UnitEnum | null $navigationGroup="Odsustva";
    protected static ?string $cluster = HumanResources::class;

    protected static ?string $modelLabel = 'Praznik';

    protected static ?string $pluralModelLabel = 'Praznici';

    protected static ?string $navigationLabel = 'Praznici';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return HolidayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HolidayTable::configure($table);
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
            'index' => HumanResources\Resources\HolidayResource\Pages\ListHolidays::route('/'),
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
