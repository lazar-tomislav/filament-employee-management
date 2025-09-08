<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas;

use Filament\Infolists;
use Filament\Schemas\Schema;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->columns(2)
            ->components([
                Infolists\Components\KeyValueEntry::make('employee_details')
                    ->hiddenLabel()
                    ->keyLabel('Naziv')
                    ->valueLabel('Vrijednost'),

            ]);
    }
}
