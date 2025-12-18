<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Tables;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Actions\EmployeeAction;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Actions\HeaderActionsPosition;
use Filament\Tables\Table;

class EmployeeTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name_email')
                    ->label("Zaposlenik")
                    ->searchable(['first_name', 'last_name', 'email']),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Broj telefona')
                    ->searchable(),
                Tables\Columns\TextColumn::make('employeeDepartment.name')
                    ->label('Odjel')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktivan')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Datum stvaranja')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Datum ažuriranja')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                Actions\ViewAction::make()->modal()->modalWidth(\Filament\Support\Enums\Width::FiveExtraLarge),
                Actions\EditAction::make()->modal()->modalWidth(\Filament\Support\Enums\Width::FiveExtraLarge)->modalHeading("Uredi zaposlenika"),
            ])
            ->headerActions([
                EmployeeAction::allEmployeTimeReportExport(),
            ])
            ->headerActionsPosition(HeaderActionsPosition::Bottom)
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }
}
