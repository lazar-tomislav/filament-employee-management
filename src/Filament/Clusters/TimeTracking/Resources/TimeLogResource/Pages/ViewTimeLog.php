<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\TimeLogResource\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\TimeLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTimeLog extends ViewRecord
{
    protected static string $resource = TimeLogResource::class;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->time_log_details = [
            // key value format for displaying details
            "Zaposlenik" => $this->record->employee?->full_name_email,
            "Datum" => $this->record->date ? $this->record->date->format('d.m.Y') : "-",
            "Broj sati" => $this->record->hours,
            "Status" => $this->record->status->getLabel(),
            "Tip unosa" => $this->record->log_type->getLabel(),
            "Kreirao zapis" => $this->record->created_by ? $this->record->created_by->full_name : "-",
            "Kreirano" => $this->record->created_at ? $this->record->created_at->format('d.m.Y H:i') : "-",
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
        return 'Pregled unosa';
    }
}
