<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Schemas;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Actions\LeaveRequestActions;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
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
                    ->state(function (LeaveRequest $record): array {
                        $hodApproval = $record->headOfDepartmentApprover
                            ? $record->headOfDepartmentApprover->full_name . ' (' . $record->approved_by_head_of_department_at?->format('d.m.Y H:i') . ')'
                            : 'Nije odobreno';

                        $directorApproval = $record->directorApprover
                            ? $record->directorApprover->full_name . ' (' . $record->approved_by_director_at?->format('d.m.Y H:i') . ')'
                            : 'Nije odobreno';

                        return [
                            'Zaposlenik' => $record->employee->full_name_email,
                            'Tip' => $record->type->getLabel(),
                            'Status' => $record->status->getLabel(),
                            'Datum početka' => $record->start_date ? $record->start_date->format('d.m.Y') : '-',
                            'Datum kraja' => $record->end_date ? $record->end_date->format('d.m.Y') : '-',
                            'Broj dana' => $record->days_count,
                            'Odgovor voditelja' => $hodApproval,
                            'Odgovor direktora' => $directorApproval,
                            'Razlog odbijanja' => $record->rejection_reason ?? 'Nije odbijeno',
                        ];
                    })
                    ->belowContent([
                        LeaveRequestActions::approveAsHeadOfDepartmentAction(),
                        LeaveRequestActions::approveAsDirectorAction(),
                        LeaveRequestActions::rejectAction(),
                        LeaveRequestActions::sendReminderAction(),
                    ])
                    ->valueLabel('Vrijednost'),

            ]);
    }
}
