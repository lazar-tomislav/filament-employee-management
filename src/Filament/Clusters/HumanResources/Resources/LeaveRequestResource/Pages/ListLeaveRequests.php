<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Pages;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;


class ListLeaveRequests extends ListRecords
{
    protected static string $resource = LeaveRequestResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Svi zahtjevi')
                ->badge(LeaveRequest::query()->count()),
            'pending' => Tab::make(LeaveRequestStatus::PENDING->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LeaveRequestStatus::PENDING))
                ->badge(LeaveRequest::query()->where('status', LeaveRequestStatus::PENDING)->count()),
            'approved' => Tab::make(LeaveRequestStatus::APPROVED->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LeaveRequestStatus::APPROVED))
                ->badge(LeaveRequest::query()->where('status', LeaveRequestStatus::APPROVED)->count()),
            'rejected' => Tab::make(LeaveRequestStatus::REJECTED->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LeaveRequestStatus::REJECTED))
                ->badge(LeaveRequest::query()->where('status', LeaveRequestStatus::REJECTED)->count()),
            'canceled' => Tab::make(LeaveRequestStatus::CANCELED->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LeaveRequestStatus::CANCELED))
                ->badge(LeaveRequest::query()->where('status', LeaveRequestStatus::CANCELED)->count()),
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
}
