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
                            'Klijent' => $record->client->name,
                            'Zadužena osoba' => $record->employee->full_name_email,
                            'Tip projekta' => $record->type->getLabel(),
                            'Status projekta' => $record->status->getLabel(),
                            'Lokacija' => $record->site_location,
                            'Iznos ugovora' => $record->contract_amount_formatted,
                            'Početak projekta' => $record->start_date?->format('d.m.Y') ?? "-",
                            'Kraj projekta' => $record->end_date?->format('d.m.Y')??"-",

                        ];
                    })
                    ->keyLabel('Naziv')
                    ->valueLabel('Vrijednost'),
            ]);
    }
}
