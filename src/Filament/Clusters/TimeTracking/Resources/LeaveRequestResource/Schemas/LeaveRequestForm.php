<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Schemas;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestType;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LeaveRequestForm
{
    public static function configure(Schema $schema, ?Employee $record = null, ?\Closure $afterStateUpdated = null): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Zaposlenik')
                    ->options(Employee::options())
                    ->hidden(fn() => $record !== null)
                    ->preload()
                    ->searchable()
                    ->required()
                    ->afterStateUpdated($afterStateUpdated)
                    ->live(),

                Grid::make()
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Datum od')
                            ->native()
                            ->required()
                            ->default(now()->addDay())
                            ->live()
                            ->afterStateUpdated($afterStateUpdated)
                            ->displayFormat('d.m.Y')
                            ->format('Y-m-d')
                            ->maxDate(fn($get) => $get('end_date')),

                        DatePicker::make('end_date')
                            ->label('Datum do')
                            ->displayFormat('d.m.Y')
                            ->native()
                            ->required()
                            ->afterStateUpdated($afterStateUpdated)
                            ->default(now()->addDay())
                            ->live()
                            ->format('Y-m-d')
                            ->minDate(fn($get) => $get('start_date')),
                    ]),

                Select::make('leave_type_option')
                    ->label('Razlog odsutnosti')
                    ->options(function (Get $get) use ($record): array {
                        $employee = $record ?? Employee::find($get('employee_id'));
                        $options = [];
                        if($employee){
                            $allowances = $employee->leaveAllowances()
                                ->whereIn('year', [now()->year, now()->subYear()])
                                ->orderBy('year', 'desc')
                                ->get();
                            foreach($allowances as $allowance){
                                $options['allowance_' . $allowance->id] = "Godišnji odmor - {$allowance->year}";
                            }
                        }
                        $options[LeaveRequestType::SICK_LEAVE->value] = 'Bolovanje';
                        $options[LeaveRequestType::PAID_LEAVE->value] = 'Plaćeni slobodan dan';

                        return $options;
                    })
                    ->required()
                    ->afterStateUpdated($afterStateUpdated)
                    ->live()
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->columnSpanFull()
                    ->label('Bilješka (opcionalno)'),
            ]);
    }
}
