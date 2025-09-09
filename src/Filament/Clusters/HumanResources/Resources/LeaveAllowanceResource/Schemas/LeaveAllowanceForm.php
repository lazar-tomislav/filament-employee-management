<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveAllowanceResource\Schemas;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Validation\Rules\Unique;

class LeaveAllowanceForm
{
    public static function configure(Schema $schema, ?Employee $employee = null): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('employee_id')
                    ->label('Zaposlenik')
                    ->relationship('employee', 'first_name')
                    ->getOptionLabelFromRecordUsing(function (?Employee $employee) {
                        return $employee->full_name_email;
                    })
                    ->columnSpanFull()
                    ->searchable()
                    ->visibleOn(Operation::Create)
                    ->preload()
                    ->required()
                    ->default($employee?->id)
                    ->helperText('Zaposlenik kojem se ručno unosi broj dana godišnjeg odmora. Ako je zaposlenik zaposlen u tijeku godine, unesite broj dana koji mu pripada za ostatak godine.'),

                Forms\Components\TextInput::make('total_days')
                    ->label('Ukupno dana')
                    ->numeric()
                    ->required()
                    ->maxValue(40)
                    ->minValue(1)
                    ->default(20)// 20 dana godišnjeg odmora
                    ->helperText('Ako se zaposlenik zapošljava u tijeku godine, unesite broj dana koji mu pripadaju za ostatak godine'),

                Forms\Components\DatePicker::make('valid_until_date')
                    ->label('Vrijedi do')
                    ->displayFormat('d.m.Y')
                    ->required()
                    ->default(fn (callable $get) => now()->setYear(($get('year') ?? now()->year) + 1)->month(6)->endOfMonth())
                    ->native(),

                Forms\Components\TextInput::make('year')
                    ->label('U koju godinu pripada broj dana godišnjeg odmora?')
                    ->numeric()
                    ->unique(
                        table: 'leave_allowances',
                        column: 'year',
                        ignoreRecord: true,
                        modifyRuleUsing: function (Unique $rule, callable $get) {
                            return $rule->where('employee_id', $get('employee_id'));
                        }
                    )
                    ->helperText('Za koju godinu se unosi broj dana godišnjeg odmora. Ako je zaposlenik zaposlen u tijeku godine, unesite godinu u kojoj je zaposlen.')
                    ->required()
                    ->columnSpanFull()
                    ->live()
                    ->default(now()->year),

                Forms\Components\Textarea::make('notes')
                    ->label('Bilješke')
                    ->columnSpanFull(),
            ]);
    }
}
