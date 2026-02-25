<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\TimeLogResource\Schemas;

use Amicus\FilamentEmployeeManagement\Enums\LogType;
use Amicus\FilamentEmployeeManagement\Enums\TimeLogStatus;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Pages\ViewEmployee;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Schemas\Components;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;

class TimeLogForm
{
    public static function configureForEmployeeView(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::getTimeComponent(),
                Components\Grid::make(2)
                    ->schema([
                        self::getWorkStartTimeComponent(),
                        self::getWorkEndTimeComponent(),
                    ]),
                self::getWorkFromHomeComponent(),
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
                    ->visible(fn () => ! request()->routeIs(ViewEmployee::getRouteName()))
                    ->helperText('Odaberite zaposlenika za kojeg unosite sate'),

                Forms\Components\DatePicker::make('date')
                    ->label('Datum')
                    ->displayFormat('d.m.Y')
                    ->required()
                    ->default(now())
                    ->visible(fn () => ! request()->routeIs(ViewEmployee::getRouteName()))
                    ->native()
                    ->helperText('Datum za koji se unose radni sati'),

                self::getTimeComponent(),
                Components\Grid::make(2)
                    ->schema([
                        self::getWorkStartTimeComponent(),
                        self::getWorkEndTimeComponent(),
                    ]),

                Forms\Components\Select::make('log_type')
                    ->label('Tip unosa')
                    ->options(LogType::class)
                    ->default(LogType::RADNI_SATI)
                    ->visible(fn () => ! request()->routeIs(ViewEmployee::getRouteName()))
                    ->required()
                    ->helperText('Odaberite tip unosa sati'),

                self::getWorkFromHomeComponent()
                    ->visible(fn () => ! request()->routeIs(ViewEmployee::getRouteName())),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(TimeLogStatus::class)
                    ->visible(fn () => ! request()->routeIs(ViewEmployee::getRouteName()))
                    ->default(TimeLogStatus::default())
                    ->required()
                    ->helperText('Planirano - za unaprijed unesene sate, Potvrđeno - za već odrađene sate'),

                self::getNoteComponent(),
            ]);
    }

    protected static function getWorkFromHomeComponent()
    {
        return Forms\Components\ToggleButtons::make('is_work_from_home')
            ->label('Rad od kuće')
            ->boolean()
            ->default(false)
            ->inline()
            ->helperText('Označite ako je rad obavljen od kuće');
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
            ->live(debounce: 1000)
            ->required()
            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                $hours = (float) $state;
                $startTime = $get('work_start_time');
                if ($hours > 0 && $startTime) {
                    $start = Carbon::parse($startTime);
                    $end = $start->copy()->addMinutes((int) round($hours * 60));
                    $set('work_end_time', $end->format('H:i'));
                }
            })
            ->suffix(function ($get) {
                $hours = (float) $get('hours');
                Log::debug('hours: ' . $hours);
                if ($hours <= 0) {
                    return '00:00';
                }
                $wholeHours = floor($hours);
                $minutes = ($hours - $wholeHours) * 60;
                $suffix = sprintf('%02d:%02d', $wholeHours, $minutes);

                return $suffix;
            })
            ->helperText('Unesite broj sati (npr. 8 ili 7.5)');
    }

    protected static function getWorkStartTimeComponent(): Forms\Components\TimePicker
    {
        $tenantService = app(\App\Services\TenantFeatureService::class);

        return Forms\Components\TimePicker::make('work_start_time')
            ->label('Početak rada')
            ->seconds(false)
            ->minutesStep(30)
            ->default($tenantService->getDefaultStartTime())
            ->live(debounce: 1000)
            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                if (! $state) {
                    return;
                }

                $rounded = self::roundToNext30Minutes($state);
                if ($rounded !== $state) {
                    $set('work_start_time', $rounded);
                    $state = $rounded;
                }

                $endTime = $get('work_end_time');
                if ($endTime) {
                    $start = Carbon::parse($state);
                    $end = Carbon::parse($endTime);
                    if ($end->gt($start)) {
                        $diffHours = abs($start->diffInMinutes($end)) / 60;
                        $set('hours', round($diffHours, 2));
                    }
                }
            })
            ->helperText('Vrijeme početka rada (zaokružuje se na 30 min)');
    }

    protected static function getWorkEndTimeComponent(): Forms\Components\TimePicker
    {
        $tenantService = app(\App\Services\TenantFeatureService::class);

        return Forms\Components\TimePicker::make('work_end_time')
            ->label('Završetak rada')
            ->seconds(false)
            ->minutesStep(30)
            ->default($tenantService->getDefaultEndTime())
            ->live(debounce: 1000)
            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                if (! $state) {
                    return;
                }

                $rounded = self::roundToNext30Minutes($state);
                if ($rounded !== $state) {
                    $set('work_end_time', $rounded);
                    $state = $rounded;
                }

                $startTime = $get('work_start_time');
                if ($startTime) {
                    $start = Carbon::parse($startTime);
                    $end = Carbon::parse($state);
                    if ($end->gt($start)) {
                        $diffHours = abs($start->diffInMinutes($end)) / 60;
                        $set('hours', round($diffHours, 2));
                    }
                }
            })
            ->helperText('Vrijeme završetka rada (zaokružuje se na 30 min)');
    }

    /**
     * Zaokružuje vrijeme na sljedeću 30-minutnu vrijednost.
     * Npr. 07:15 → 07:30, 07:45 → 08:00, 07:00 → 07:00
     */
    protected static function roundToNext30Minutes(string $time): string
    {
        $parsed = Carbon::parse($time);
        $minutes = $parsed->minute;

        if ($minutes === 0 || $minutes === 30) {
            return $parsed->format('H:i');
        }

        if ($minutes < 30) {
            $parsed->minute(30);
        } else {
            $parsed->minute(0)->addHour();
        }

        return $parsed->format('H:i');
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
