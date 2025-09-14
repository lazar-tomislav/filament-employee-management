<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Tables;

use Amicus\FilamentEmployeeManagement\Enums\TaskStatus;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Pages\ViewEmployee;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Pages\ViewProject;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Actions\TaskAction;
use Amicus\FilamentEmployeeManagement\Models\Task;
use App\Filament\Resources\Clients\Pages\ViewClient;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('title')
                    ->label('Zadatak')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('client.name')
                    ->label('Klijent')
                    ->searchable()
                    ->url(fn($record) => ViewClient::getUrl(['record' => $record->client_id]))
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Projekt')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => $record->project_id ? ViewProject::getUrl(['record' => $record->project_id]) : null)
                    ->placeholder('Jednokratni zadatak'),

                TextColumn::make('assignee.first_name')
                    ->formatStateUsing(fn($record) => $record->assignee->full_name_email)
                    ->label('Zadužena osoba')
                    ->searchable()
                    ->url(fn($record) => $record->assignee ? ViewEmployee::getUrl(['record' => $record->assignee_id]) : null)
                    ->sortable()
                    ->placeholder('Nedodjeljeno'),

                TextColumn::make('due_at')
                    ->label('Rok')
                    ->date('d.m.Y')
                    ->sortable()
                    ->placeholder('Nije postavljen'),

                IconColumn::make('is_billable')
                    ->label('Naplativi')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('billed_amount')
                    ->label('Iznos (€)')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: ',',
                        thousandsSeparator: '.'
                    )
                    ->sortable()
                    ->placeholder('0,00')
                    ->visible(fn($record) => $record?->is_billable ?? false),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                TaskAction::editInCustomModal($table),
                TaskAction::changeStatusAction($table),
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
