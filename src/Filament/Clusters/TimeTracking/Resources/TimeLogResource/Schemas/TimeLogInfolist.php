<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\TimeLogResource\Schemas;

use Filament\Infolists;
use Filament\Schemas\Schema;

class TimeLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
              Infolists\Components\KeyValueEntry::make("time_log_details") ->hiddenLabel()
                  ->keyLabel('Naziv')
                  ->valueLabel('Vrijednost'),
            ]);
    }
}
