<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\HolidayResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                    Forms\Components\TextInput::make('name')
                        ->label('Naziv')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('date')
                        ->label('Datum')
                        ->required(),
                    Forms\Components\Toggle::make('is_recurring')
                        ->label('Ponavljajući praznik')
                        ->required()
                        ->helperText('Označavanjem ove opcije, praznik će se automatski ponavljati svake godine na odabrani datum.'),
            ]);
    }
}
