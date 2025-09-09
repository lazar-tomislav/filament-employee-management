<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Tables;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Actions\LeaveRequestActions;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Schemas\LeaveRequestForm;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Schemas\LeaveRequestInfolist;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Tables;
use Filament\Tables\Table;

class LeaveRequestTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name_email')
                    ->label('Zatražio')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tip'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Datum odsutnosti')
                    ->formatStateUsing(function ($record) {
                        return $record->start_date->format('d.m.Y') . ' - ' . $record->end_date->format('d.m.Y');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_count')
                    ->label('Broj dana')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status'),


                Tables\Columns\TextColumn::make('approver.full_name')
                    ->label('Odobrio')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Actions\ViewAction::make()
                        ->schema(fn($schema) => LeaveRequestInfolist::configure($schema))
                        ->slideOver(),
                    LeaveRequestActions::approveAction(),
                    LeaveRequestActions::rejectAction(),
                ])
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }
}
