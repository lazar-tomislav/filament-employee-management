<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Tables;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Actions\LeaveRequestActions;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Schemas\LeaveRequestInfolist;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class LeaveRequestTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordAction('view')
            ->recordUrl(null)
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
                        return $record->start_date->format('d.m.Y').' - '.$record->end_date->format('d.m.Y');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_count')
                    ->label('Broj dana')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status'),

                Tables\Columns\IconColumn::make('approved_by_head_of_department_id')
                    ->label('Voditelj')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn (LeaveRequest $record): ?string => $record->headOfDepartmentApprover?->full_name),

                Tables\Columns\IconColumn::make('approved_by_director_id')
                    ->label('Direktor')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn (LeaveRequest $record): ?string => $record->directorApprover?->full_name),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Actions\ViewAction::make()
                        ->schema(fn ($schema) => LeaveRequestInfolist::configure($schema))
                        ->modal()->modalWidth(Width::FiveExtraLarge),
                    Actions\EditAction::make()
                        ->label('Uredi (admin)')
                        ->visible(function (LeaveRequest $record): bool {
                            /** @var User|null $user */
                            $user = auth()->user();

                            if (! $user) {
                                return false;
                            }

                            if ($user->canSeeAllLeave()) {
                                return true;
                            }

                            return $user->hodDepartmentIds()->contains($record->employee?->department_id);
                        }),
                    LeaveRequestActions::approveAsHeadOfDepartmentAction(),
                    LeaveRequestActions::rejectAsHeadOfDepartmentAction(),
                    LeaveRequestActions::approveAsDirectorAction(),
                    LeaveRequestActions::rejectAction(),
                    LeaveRequestActions::sendReminderAction(),
                    LeaveRequestActions::downloadPdfAction(),
                    LeaveRequestActions::overrideStatusAction(),
                    LeaveRequestActions::deletePendingAction(),
                    LeaveRequestActions::deleteApprovedAction(),
                ]),
            ]);
    }
}
