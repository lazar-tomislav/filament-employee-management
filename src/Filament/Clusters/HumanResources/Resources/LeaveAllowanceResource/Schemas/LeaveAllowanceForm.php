<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveAllowanceResource\Schemas;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class LeaveAllowanceForm
{
    public static function configure(Schema $schema, ?Employee $record = null): Schema
    {
        return $schema
            ->components([
                Repeater::make('leaveAllowances')
                    ->label('Godišnji odmor po godinama')
                    ->relationship('leaveAllowances')
                    ->defaultItems(1)
                    ->columns(2)
                    ->columnSpanFull()
                    ->addActionLabel('Unesi za nadolazeću godinu')
                    ->itemLabel(function (array $state): string {
                        return 'Godina: '.($state['year'] ?? '—');
                    })
                    ->schema([
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
                                'leave_allowances',
                                'year',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule) => $record ? $rule->where('employee_id', $record->id) : $rule
                            )
                            ->helperText('Za koju godinu se unosi broj dana godišnjeg odmora. Ako je zaposlenik zaposlen u tijeku godine, unesite godinu u kojoj je zaposlen.')
                            ->required()
                            ->columnSpanFull()
                            ->live()
                            ->default(now()->year),

                        Forms\Components\Textarea::make('notes')
                            ->label('Bilješke')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
