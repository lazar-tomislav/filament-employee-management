<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Tables;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Pages\ViewEmployee;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages\ViewProject;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Resources\Clients\Pages\ViewClient;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordUrl(fn($record) => ViewProject::getUrl(['record' => $record->id]))
            ->columns([
                TextColumn::make('name')
                    ->label('Naziv projekta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.name')
                    ->label('Klijent')
                    ->searchable()
                    ->url(fn($record) => ViewClient::getUrl(['record' => $record->id]))
                    ->sortable(),

                TextColumn::make('employee.first_name')
                    ->label('Osoba')
                    ->formatStateUsing(function ($record) {
                        return $record->employee->full_name;
                    })
                    ->url(fn($record) => ViewEmployee::getUrl(['record' => $record->employee->id]))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->sortable(),
//
//                TextColumn::make('status')
//                    ->label('Status')
//                    ->badge()
//                    ->sortable(),

                TextColumn::make('site_location')
                    ->label('Gradilište')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('contract_amount')
                    ->label('Vrijednost (€)')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: ',',
                        thousandsSeparator: '.'
                    )
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Početak')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Završetak')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Kreiran')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Ažuriran')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Obrisan')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->slideOver()
                    ->fillForm(fn($record) => $record->toArray())
                    ->schema(fn($schema) => ProjectForm::configure($schema))
                    ->modalHeading("Pregled projekta"),

                EditAction::make()
                    ->slideOver()
                    ->schema(fn($schema) => ProjectForm::configure($schema))
                    ->modalHeading("Uredi projekt"),

                DeleteAction::make()->hiddenLabel()->modalHeading("Obriši projekt"),
            ])
            ->emptyStateHeading("Nema projekata")
            ->emptyStateDescription("Dodajte projekte kako biste ih vidjeli ovdje.")
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
