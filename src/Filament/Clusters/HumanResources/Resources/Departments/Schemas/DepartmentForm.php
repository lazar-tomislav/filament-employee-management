<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\Departments\Schemas;

use Filament\Forms\Components\TextInput;
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
            ]);
    }
}
