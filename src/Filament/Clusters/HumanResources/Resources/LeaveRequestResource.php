<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Schemas\LeaveRequestInfolist;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Tables\LeaveRequestTable;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationLabel = 'Zahtjevi za odsustvo';

    protected static string | UnitEnum | null $navigationGroup = 'Odsustva';

    protected static ?string $modelLabel = 'Zahtjev';

    protected static ?string $pluralModelLabel = 'Zahtjevi';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?int $navigationSort = 25;

    protected static ?string $cluster = HumanResources::class;

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
            'index' => LeaveRequestResource\Pages\ListLeaveRequests::route('/'),
            'create' => LeaveRequestResource\Pages\CreateLeaveRequest::route('/create'),
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

    public static function getEloquentQuery(): Builder
    {
        return static::scopeForCurrentUser(parent::getEloquentQuery());
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
                $q->where('employee_id', $employeeId);
            } else {
                $q->whereRaw('1 = 0');
            }

            if ($hodDeptIds->isNotEmpty()) {
                $q->orWhereHas('employee', fn (Builder $sub) => $sub->whereIn('department_id', $hodDeptIds));
            }
        });
    }
}
