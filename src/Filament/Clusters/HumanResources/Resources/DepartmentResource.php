<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\Departments\Pages\ListDepartments;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\Departments\Schemas\DepartmentForm;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\Departments\Tables\DepartmentTable;
use Amicus\FilamentEmployeeManagement\Models\Department;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $cluster = HumanResources::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Odjeli zaposlenika';

    protected static ?string $modelLabel = 'Odjel zaposlenika';

    protected static ?string $pluralModelLabel = 'Odjeli zaposlenika';

    protected static string|UnitEnum|null $navigationGroup = "Ostalo";

    protected static ?int $navigationSort = 80;

    public static function form(Schema $schema): Schema
    {
        return DepartmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartmentTable::table($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDepartments::route('/'),
        ];
    }
}
