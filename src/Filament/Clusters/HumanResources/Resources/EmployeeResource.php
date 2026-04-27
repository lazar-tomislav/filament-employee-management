<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas\EmployeeForm;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Tables\EmployeeTable;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $cluster = HumanResources::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Zaposlenici';

    protected static ?string $recordTitleAttribute = 'first_name';

    protected static ?string $modelLabel = 'zaposlenika';

    protected static ?string $pluralLabel = 'zaposlenici';

    protected static ?string $label = 'zaposlenik';

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeTable::configure($table)
            ->modifyQueryUsing(fn (Builder $query) => static::scopeForCurrentUser($query));
    }

    public static function getBreadcrumb(): string
    {
        return 'Zaposlenici';
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
            'index' => EmployeeResource\Pages\ListEmployees::route('/'),
            'create' => EmployeeResource\Pages\CreateEmployee::route('/create'),
            'view' => EmployeeResource\Pages\ViewEmployee::route('/{record}'),
            'edit' => EmployeeResource\Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::scopeForCurrentUser(
            parent::getRecordRouteBindingEloquentQuery()
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ])
        );
    }

    public static function scopeForCurrentUser(Builder $query): Builder
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->canSeeAllLeave()) {
            return $query;
        }

        $employeeId = $user->employee?->id;
        $hodDeptIds = $user->hodDepartmentIds();

        return $query->where(function (Builder $q) use ($employeeId, $hodDeptIds) {
            if ($employeeId) {
                $q->where('id', $employeeId);
            } else {
                $q->whereRaw('1 = 0');
            }

            if ($hodDeptIds->isNotEmpty()) {
                $q->orWhereIn('department_id', $hodDeptIds);
            }
        });
    }
}
