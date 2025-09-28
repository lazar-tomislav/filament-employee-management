<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Tables;

use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages\ViewProject;
use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Filament\Tables\Columns\DatePickerColumn;
use App\Filament\Tables\Columns\InitialsColumn;
use App\Filament\Tables\Columns\StatusSelectColumn;
use App\Filament\Tables\Columns\TaskNameColumn;
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

                StatusSelectColumn::make('project_status')
                    ->enum(StatusProjekta::class)
                    ->label('Faza projekta')
                    ->width("8rem")
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('project.name')
                    ->label('Projekt')
                    ->grow(false)->weight('semibold')
                    ->alignCenter()
                    ->width("8rem")
                    ->url(fn($record) => $record->project_id ? ViewProject::getUrl(['record' => $record->project_id]) : null)
                    ->placeholder('Jednokratni zadatak'),

                TextColumn::make('client.name')
                    ->label('Klijent')->weight('semibold')
                    ->url(fn($record) => ViewClient::getUrl(['record' => $record->client_id])),
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
