<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Tables;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Pages\ViewEmployee;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages\ViewProject;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Schemas\ProjectForm;
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
            ->columns([
                TextColumn::make('name')
                    ->label('Naziv projekta')
                    ->searchable()->weight('semibold')
                    ->sortable(),

                TextColumn::make('employee.first_name')
                    ->label('Osoba')
                    ->formatStateUsing(function ($record) {
                        return $record->employee->full_name;
                    })
                    ->url(fn($record) => ViewEmployee::getUrl(['record' => $record->employee->id]))
                    ->searchable()
                    ->sortable(),

                TextColumn::make("description")
                    ->label('Opis')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('created_at')
                    ->label('Kreiran')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

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

                EditAction::make()
                    ->modal()->modalWidth(\Filament\Support\Enums\Width::FiveExtraLarge)
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
