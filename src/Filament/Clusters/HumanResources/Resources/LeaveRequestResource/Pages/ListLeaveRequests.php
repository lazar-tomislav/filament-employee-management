<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Pages;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource;
use Amicus\FilamentEmployeeManagement\Models\Department;
use App\Models\User;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLeaveRequests extends ListRecords
{
    protected static string $resource = LeaveRequestResource::class;

    public function getTabs(): array
    {
        $base = fn (): Builder => LeaveRequestResource::getEloquentQuery();

        return [
            'all' => Tab::make('Svi zahtjevi')
                ->badge($base()->count())
                ->badgeColor('gray'),
            'pending' => Tab::make(LeaveRequestStatus::PENDING->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LeaveRequestStatus::PENDING))
                ->badge($base()->where('status', LeaveRequestStatus::PENDING)->count())
                ->badgeColor(LeaveRequestStatus::PENDING->getColor()),
            'approved' => Tab::make(LeaveRequestStatus::APPROVED->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LeaveRequestStatus::APPROVED))
                ->badge($base()->where('status', LeaveRequestStatus::APPROVED)->count())
                ->badgeColor(LeaveRequestStatus::APPROVED->getColor()),
            'rejected' => Tab::make(LeaveRequestStatus::REJECTED->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LeaveRequestStatus::REJECTED))
                ->badge($base()->where('status', LeaveRequestStatus::REJECTED)->count())
                ->badgeColor(LeaveRequestStatus::REJECTED->getColor()),
            'canceled' => Tab::make(LeaveRequestStatus::CANCELED->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LeaveRequestStatus::CANCELED))
                ->badge($base()->where('status', LeaveRequestStatus::CANCELED)->count())
                ->badgeColor(LeaveRequestStatus::CANCELED->getColor()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function getTitle(): string
    {
        return 'Pregled zahtjeva';
    }

    public function getSubheading(): ?string
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user || $user->canSeeAllLeave()) {
            return null;
        }

        $hodDeptIds = $user->hodDepartmentIds();

        if ($hodDeptIds->isEmpty()) {
            return null;
        }

        $names = Department::query()
            ->whereIn('id', $hodDeptIds)
            ->pluck('name')
            ->implode(', ');

        return "Vidite samo zaposlenike svog odjela: {$names}";
    }
}
