<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Schemas;

use Filament\Infolists;
use Filament\Schemas\Schema;

class LeaveRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $record = $schema->getRecord();
        $record->leave_request_details = [
            'Zaposlenik' => $record->employee->full_name_email,
            'Tip' => $record->type->getLabel(),
            'Status' => $record->status->getLabel(),
            'Datum početka' => $record->start_date ? $record->start_date->format('d.m.Y') : "-",
            'Datum kraja' => $record->end_date ? $record->end_date->format('d.m.Y') : "-",
            'Broj dana' => $record->days_count,
            'Odobrio' => $record->approver->full_name,
        ];
        return $schema
            ->components([
                Infolists\Components\KeyValueEntry::make('leave_request_details')
                    ->hiddenLabel()
                    ->keyLabel('Naziv')
                    ->valueLabel('Vrijednost'),

            ]);
    }
}
