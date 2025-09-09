<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLeaveRequest extends ViewRecord
{
    protected static string $resource = LeaveRequestResource::class;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->leave_request_details = [
            'Zaposlenik' => $this->record->employee->full_name_email,
            'Tip' => $this->record->type->getLabel(),
            'Status' => $this->record->status->getLabel(),
            'Datum početka' => $this->record->start_date ? $this->record->start_date->format('d.m.Y') : "-",
            'Datum kraja' => $this->record->end_date ? $this->record->end_date->format('d.m.Y') : "-",
            'Broj dana' => $this->record->days_count,
            'Odobrio' => $this->record->approver->full_name,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return 'Pregled zahtjeva';
    }
}
