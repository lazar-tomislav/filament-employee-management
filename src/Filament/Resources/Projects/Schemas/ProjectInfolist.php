<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Schemas\Schema;

class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                KeyValueEntry::make("project_info_details")
                    ->hiddenLabel()
                    ->state(function ($record) {
                        return [
                            'Naziv projekta' => $record->name,
                            'Zadužena osoba' => $record->employee->full_name_email,
                            "Opis"=> $record->description,
                        ];
                    })
                    ->keyLabel('Naziv')
                    ->valueLabel('Vrijednost'),
            ]);
    }
}
