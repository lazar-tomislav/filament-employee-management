<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveAllowanceResource\Tables;

use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeaveAllowanceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_days')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
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
                    ->state(fn ($record) => $record->total_days - $record->used_days)
                    ->numeric()
                    ->color(fn ($state) => $state <= 5 ? 'warning' : 'primary'),

                TextColumn::make('valid_until_date')
                    ->label('Iskoristivo do')
                    ->date('d.m.Y')
                    ->sortable(),
            ]);
    }
}
