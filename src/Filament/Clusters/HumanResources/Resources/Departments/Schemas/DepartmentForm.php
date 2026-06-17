<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\Departments\Schemas;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Naziv odjela')
                    ->placeholder('npr. IT odjel, Financije, Prodaja')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Naziv organizacijske jedinice')
                    ->prefixIcon('heroicon-o-building-office-2')
                    ->autofocus(),

                Select::make('head_of_department_employee_id')
                    ->label('Voditelj odjela')
                    ->options(Employee::options())
                    ->searchable()
                    ->placeholder('Odaberi voditelja')
                    ->helperText('Voditelju odjela stižu na odobrenje zahtjevi za godišnje odmore.')
                    ->prefixIcon('heroicon-o-user-circle'),

                Toggle::make('can_see_all_employees')
                    ->label('Uvid u sve zaposlenike')
                    ->helperText('Svi zaposlenici ovog odjela vide sve zaposlenike (npr. odjel administracije).')
                    ->default(false),
            ]);
    }
}
