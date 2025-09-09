<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Schemas;

use Filament\Infolists;
use Filament\Schemas\Schema;

class LeaveRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Infolists\Components\KeyValueEntry::make('leave_request_details')
                    ->hiddenLabel()
                    ->keyLabel('Naziv')
                    ->valueLabel('Vrijednost'),

            ]);
    }
}
