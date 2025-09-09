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
                Infolists\Components\KeyValueEntry::make("time_log_details")->hiddenLabel()
                    ->state(function ($record) {
                        return [
                            // key value format for displaying details
                            "Zaposlenik" => $record->employee->full_name_email,
                            "Unos za dan" => $record->date ? $record->date->format('d.m.Y') : "-",
                            "Broj sati" => $record->hours,
                            "Status" => $record->status->getLabel(),
                            "Tip unosa" => $record->log_type->getLabel(),
                            "Opis" => $record->description ?? "-",
                            "Kreirano" => $record->created_at ? $record->created_at->format('d.m.Y H:i') : "-",
                        ];
                    })
                    ->keyLabel('Naziv')
                    ->valueLabel('Vrijednost'),
            ]);
    }
}
