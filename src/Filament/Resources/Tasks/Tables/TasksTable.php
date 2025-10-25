<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Tables;

use Amicus\FilamentEmployeeManagement\Filament\Tables\Columns\DatePickerColumn;
use Amicus\FilamentEmployeeManagement\Filament\Tables\Columns\InitialsColumn;
use Amicus\FilamentEmployeeManagement\Filament\Tables\Columns\StatusSelectColumn;
use Amicus\FilamentEmployeeManagement\Filament\Tables\Columns\TaskNameColumn;
use Amicus\FilamentEmployeeManagement\Enums\TaskStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TaskNameColumn::make('title')
                    ->label('Zadatak')
                    ->width("25rem")
                    ->alignCenter()
                    ->grow(false),

                InitialsColumn::make('assignee.initials')
                    ->label('Zadužen')
                    ->width("3rem")
                    ->extraCellAttributes(['class'=>"initials-column"])
                    ->alignCenter()
                    ->grow(false),

                DatePickerColumn::make('due_at')
                    ->grow(false)
                    ->label('Rok')
                    ->extraCellAttributes(['class'=>"datetime-column"])
                    ->alignCenter()
                    ->width("8rem"),

                StatusSelectColumn::make('status')
                    ->enum(TaskStatus::class)
                    ->label('Status')
                    ->grow(false)
                    ->alignCenter()
                    ->width("8rem"),

                TextColumn::make('project.name')
                    ->label('Projekt')
                    ->grow(false)->weight('semibold')
                    ->alignCenter()
                    ->width("8rem")
                    ->placeholder('Jednokratni zadatak'),
            ])
            ->recordActions([
                DeleteAction::make()->hiddenLabel()->modalHeading("Obriši zadatak"),
            ])
            ->emptyStateHeading("Nema zadataka")
            ->emptyStateDescription("Dodajte zadatke kako biste ih vidjeli ovdje.")
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
