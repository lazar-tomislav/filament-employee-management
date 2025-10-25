<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Schemas;

use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use App\Enums\TipProjekta;
use App\Filament\Resources\Clients\Schemas\ClientForm;
use App\Filament\Resources\Offers\Schemas\ConstructionObjectForm;
use App\Models\Project;
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
                    ->placeholder('Novi projekt')
                    ->columnSpanFull()
                    ->helperText('Unesite naziv projekta')
                    ->required(),

                Select::make('employee_id')
                    ->label('Zadužena osoba')
                    ->placeholder('Odaberite zaposlenika')
                    ->options(Employee::options())
                    ->preload()
                    ->searchable()
                    ->columnSpanFull()
                    ->preload()
                    ->required(),
                Textarea::make('description')
                    ->label('Opis projekta / Kratke bilješke')
                    ->placeholder('Detaljni opis projekta, specifikacije, napomene...')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public static function projectIdSelect(): Select
    {
        return Select::make('project_id')
            ->label('Projekt')
            ->options(\Amicus\FilamentEmployeeManagement\Models\Project::options())
            ->searchable()
            ->preload();
    }

}
