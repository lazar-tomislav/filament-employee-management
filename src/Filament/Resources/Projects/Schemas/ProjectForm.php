<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Schemas;

use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use App\Enums\TipProjekta;
use App\Filament\Resources\Clients\Schemas\ClientForm;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Naziv projekta')
                    ->placeholder('Solarni sustav za Neteko d.o.o.')
                    ->helperText('Unesite naziv projekta')
                    ->required(),

                ClientForm::clientSelect()->searchable(),

                Select::make('employee_id')
                    ->label('Zadužena osoba')
                    ->placeholder('Odaberite zaposlenika')
                    ->options(Employee::options())
                    ->preload()
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('type')
                    ->label('Tip projekta')
                    ->placeholder('Odaberite tip projekta')
                    ->options(TipProjekta::class)
                    ->default(TipProjekta::Tvrtke->value)
                    ->required(),

                Select::make('status')
                    ->label('Status projekta')
                    ->placeholder('Odaberite status')
                    ->options(StatusProjekta::class)
                    ->default(StatusProjekta::Priprema->value)
                    ->required(),

                TextInput::make('site_location')
                    ->label('Gradilište')
                    ->placeholder('Zagreb, Savska cesta 123')
                    ->required(),

                TextInput::make('contract_amount')
                    ->label('Vrijednost ugovora (€)')
                    ->placeholder('25000.00')
                    ->helperText('Ukupna vrijednost ugovora u eurima (dogovorena cijena)')
                    ->numeric()
                    ->columnSpanFull()
                    ->prefix("€ ")
                    ->inputMode('decimal')
                    ->required(),

                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Datum početka')
                            ->default(now())
                            ->placeholder('Planirani datum početka')
                            ->helperText('Planirani datum početka projekta')
                            ->maxDate(fn($get) => $get('end_date'))
                            ->live(),

                        DatePicker::make('end_date')
                            ->label('Datum završetka')
                            ->placeholder('Planirani datum završetka')
                            ->helperText('Planirani datum završetka projekta')
                            ->minDate(fn($get) => $get('start_date'))
                            ->live(),
                    ]),

                Textarea::make('description')
                    ->label('Opis projekta / Kratke bilješke')
                    ->placeholder('Detaljni opis projekta, specifikacije, napomene...')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }


}
