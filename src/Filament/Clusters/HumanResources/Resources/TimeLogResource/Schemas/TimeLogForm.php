<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\TimeLogResource\Schemas;

use Amicus\FilamentEmployeeManagement\Enums\LogType;
use Amicus\FilamentEmployeeManagement\Enums\TimeLogStatus;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Pages\ViewEmployee;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;

class TimeLogForm
{
    public static function configureForEmployeeView(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::getTimeComponent(),
                self::getNoteComponent(),
            ]);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('employee_id')
                    ->label('Zaposlenik')
                    ->options(Employee::options())
                    ->searchable()
                    ->required()
                    ->visible(fn() => !request()->routeIs(ViewEmployee::getRouteName()))
                    ->helperText('Odaberite zaposlenika za kojeg unosite sate'),

                Forms\Components\DatePicker::make('date')
                    ->label('Datum')
                    ->displayFormat('d.m.Y')
                    ->required()
                    ->default(now())
                    ->visible(fn() => !request()->routeIs(ViewEmployee::getRouteName()))
                    ->native()
                    ->helperText('Datum za koji se unose radni sati'),

                self::getTimeComponent(),

                Forms\Components\Select::make('log_type')
                    ->label('Tip unosa')
                    ->options(LogType::class)
                    ->default(LogType::RADNI_SATI)
                    ->visible(fn() => !request()->routeIs(ViewEmployee::getRouteName()))
                    ->required()
                    ->helperText('Odaberite tip unosa sati'),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(TimeLogStatus::class)
                    ->visible(fn() => !request()->routeIs(ViewEmployee::getRouteName()))
                    ->default(TimeLogStatus::default())
                    ->required()
                    ->helperText('Planirano - za unaprijed unesene sate, Potvrđeno - za već odrađene sate'),

                self::getNoteComponent()
            ]);
    }

    protected static function getTimeComponent()
    {
        return Forms\Components\TextInput::make('hours')
            ->label('Broj sati')
            ->numeric()
            ->step('0.25')
            ->minValue(0)
            ->maxValue(24)
            ->placeholder('8.0')
            ->live()
            ->required()
            ->suffix(function ($get) {
                $hours = floatval($get('hours'));
                Log::debug("hours: " . $hours);
                if($hours <= 0) return "00:00";
                $wholeHours = floor($hours);
                $minutes = ($hours - $wholeHours) * 60;
                $suffix = sprintf('%02d:%02d', $wholeHours, $minutes);
                return $suffix;
            })
            ->helperText('Unesite broj sati (npr. 8 ili 7.5)');
    }

    protected static function getNoteComponent()
    {
        return Forms\Components\Textarea::make('description')
            ->label('Opis')
            ->placeholder('Unesite kratki opis rada...')
            ->rows(3)
            ->columnSpanFull()
            ->helperText('Kratki opis rada ili aktivnosti (opcionalno)');
    }
}
