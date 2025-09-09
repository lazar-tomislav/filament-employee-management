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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Korisničko ime')
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Ime')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Prezime')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail adresa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Broj telefona')
                    ->searchable(),
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
                Actions\ViewAction::make(),
                Actions\EditAction::make()->slideOver(),
            ])
            ->headerActions([
                EmployeeAction::allEmployeTimeReportExport(),
            ])
            ->headerActionsPosition(HeaderActionsPosition::Bottom)
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }
}
