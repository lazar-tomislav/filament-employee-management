<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Schemas;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Actions\LeaveRequestActions;
use Filament\Infolists;
use Filament\Schemas\Schema;

class LeaveRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Infolists\Components\KeyValueEntry::make('leave_request_details')
                    ->hiddenLabel()
                    ->keyLabel('Naziv')
                    ->state(function ($record) {
                        return [
                            'Zaposlenik' => $record->employee->full_name_email,
                            'Tip' => $record->type->getLabel(),
                            'Status' => $record->status->getLabel(),
                            'Datum početka' => $record->start_date ? $record->start_date->format('d.m.Y') : "-",
                            'Datum kraja' => $record->end_date ? $record->end_date->format('d.m.Y') : "-",
                            'Broj dana' => $record->days_count,
                            'Odobrio' => $record->approver?->full_name ?? "Nije odobreno",
                            "Razlog Odbijanja" => $record->rejection_reason ?? "Nije odbijeno",
                        ];
                    })
                    ->belowContent([
                        LeaveRequestActions::approveAction(),
                        LeaveRequestActions::rejectAction(),
                    ])
                    ->valueLabel('Vrijednost'),

            ]);
    }
}
