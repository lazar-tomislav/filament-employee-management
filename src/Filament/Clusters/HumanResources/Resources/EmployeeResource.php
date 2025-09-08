<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas\EmployeeForm;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Tables\EmployeeTable;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use App\Filament\Clusters\HumanResources\Resources\EmployeeResource\Pages;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $cluster = HumanResources::class;

    protected static ?string $recordTitleAttribute = 'first_name';

    protected static ?string $modelLabel="zaposlenika";
    protected static ?string $pluralLabel="zaposlenici";
    protected static ?string $label = 'zaposlenik';
    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeTable::configure($table)
            ->modifyQueryUsing(function (Builder $query) {
                // if user is employee, show only their own record
                if (auth()->user()->isEmployee()) {
                    $query->where('id', auth()->user()->employee->id);
                }
            });
    }

    public static function getBreadcrumb(): string
    {
        return "Zaposlenici";
    }
    protected static ?string $navigationLabel="Zaposlenici";


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => HumanResources\Resources\EmployeeResource\Pages\ListEmployees::route('/'),
            'create' => HumanResources\Resources\EmployeeResource\Pages\CreateEmployee::route('/create'),
            'view' => HumanResources\Resources\EmployeeResource\Pages\ViewEmployeeCustom::route('/{record}'),
            'edit' => HumanResources\Resources\EmployeeResource\Pages\EditEmployee::route('/{record}/edit'),
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
