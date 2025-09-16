<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Tables;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Pages\ViewEmployee;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages\ViewProject;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Actions\TaskAction;
use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Filament\Tables\Columns\DatePickerColumn;
use App\Filament\Tables\Columns\InitialsColumn;
use App\Filament\Tables\Columns\TaskNameColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
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
                    ->alignCenter()
                    ->width("8rem"),

                TextColumn::make('project.name')
                    ->label('Projekt')
                    ->grow(false)
                    ->alignCenter()
                    ->width("8rem")
                    ->url(fn($record) => $record->project_id ? ViewProject::getUrl(['record' => $record->project_id]) : null)
                    ->placeholder('Jednokratni zadatak'),

                TextColumn::make('client.name')
                    ->label('Klijent')
                    ->url(fn($record) => ViewClient::getUrl(['record' => $record->client_id])),
            ])
            ->recordActions([
                TaskAction::changeStatusAction($table)->label("")->hiddenLabel(),
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
