<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveAllowanceResource\Tables;

use Amicus\FilamentEmployeeManagement\Models\LeaveAllowance;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeaveAllowanceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultGroup( Tables\Grouping\Group::make("year")->label("Godina"))
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->formatStateUsing(fn($record) => $record->employee->full_name . " (" . $record->employee->email . ")"),
                Tables\Columns\TextColumn::make('year')
                    ->label("Godina"),

                Tables\Columns\TextColumn::make('total_days')
                    ->label('Ukupno dana G.O')
                    ->numeric(),

                Tables\Columns\TextColumn::make('used_days')
                    ->state(fn($record) => $record->used_days)
                    ->label('Iskorišteno dana')
                    ->numeric(),

                Tables\Columns\TextColumn::make('remaining_days')
                    ->state(fn($record) => $record->total_days - $record->used_days)
                    ->label('Preostalo dana')
                    ->numeric()
                    ->color(fn($state) => $state <= 5 ? 'danger' : 'success'),
            ])
            ->filters([
                //year filter
                Tables\Filters\SelectFilter::make('year')
                    ->options(fn() => LeaveAllowance::query()
                        ->select('year')
                        ->distinct()
                        ->pluck('year', 'year'))
                    ->label('Godina')
                    ->placeholder('Sve godine'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->searchable()
            ->recordActions([
                Actions\ViewAction::make()
                    ->modalHeading("Pregled godišnjeg odmora")
                ->slideOver(),
                Actions\EditAction::make()->slideOver()->modalHeading("Uredi godišnji odmor"),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function configureEmployeeView(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort("year", 'desc')
            ->heading('Godišnji odmor')
            ->columns([
                TextColumn::make('year')
                    ->label('Godina')
                    ->sortable(),

                TextColumn::make('total_days')
                    ->label('Ukupno dana G.O')
                    ->numeric(),

                TextColumn::make('used_days')
                    ->label('Iskorišteno dana')
                    ->numeric(),

                TextColumn::make('remaining_days')
                    ->label('Preostalo dana')
                    ->state(fn($record) => $record->total_days - $record->used_days)
                    ->numeric()
                    ->color(fn($state) => $state <= 5 ? 'warning' : 'primary'),

                TextColumn::make('valid_until_date')
                    ->label('Iskoristivo do')
                    ->date('d.m.Y')
                    ->sortable(),
            ]);
    }
}
