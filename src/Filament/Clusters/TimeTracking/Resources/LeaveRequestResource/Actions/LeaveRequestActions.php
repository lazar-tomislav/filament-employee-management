<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Actions;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class LeaveRequestActions
{

    public static function approveAction(): Action
    {
        return \Filament\Actions\Action::make('approve')
            ->icon(Heroicon::OutlinedCheck)
            ->label('Odobri zahtjev')
            ->color('success')
            ->visible(fn($record)=> auth()->user()->isUredAdministrativnoOsoblje() && $record->status == LeaveRequestStatus::PENDING)
            ->requiresConfirmation()
            ->modalDescription(function($record){
                return "Odobrit ćete {$record->employee->full_name_email} zahtjev za odsustvo u periodu od {$record->start_date->format('d.m.Y')} do {$record->end_date->format('d.m.Y')}";
            })
            ->action(function (LeaveRequest $record) {
                $record->update([
                    'status' => LeaveRequestStatus::APPROVED->value,
                    'approved_by' => auth()->id(),
                ]);

                \Filament\Notifications\Notification::make()
                    ->title('Zahtjev odobren')
                    ->body('Zaposlenik je obaviješten o promjeni statusa.')
                    ->success()
                    ->send();
            });
    }

    public static function rejectAction(): Action
    {
        return \Filament\Actions\Action::make('reject')
            ->label('Odbij zahtjev')
            ->icon(Heroicon::OutlinedXCircle)
            ->visible(fn($record)=> auth()->user()->isUredAdministrativnoOsoblje() && $record->status == LeaveRequestStatus::PENDING)
            ->color('danger')
            ->schema([
                Textarea::make('rejection_reason')
                    ->label('Razlog odbijanja')
                    ->helperText("Zaposlenik će primiti obavijest o odbijanju zahtjeva s razlogom.")
                    ->required(),
            ])
            ->slideOver()
            ->action(function (LeaveRequest $record, array $data) {
                $record->update([
                    'status' => LeaveRequestStatus::REJECTED->value,
                    'approved_by' => auth()->id(),
                    'rejection_reason' => $data['rejection_reason'],
                ]);
                Notification::make()
                    ->title('Zahtjev obijen')
                    ->body('Zaposlenik je obaviješten o promjeni statusa.')
                    ->success()
                    ->send();
            });
    }

    public static function cancelRequestAction(): Action
    {
        return \Filament\Actions\Action::make('cancel_request')
            ->label('Otkaži zahtjev')
            ->icon(Heroicon::OutlinedXMark)
            ->color('danger')
            ->visible(function (LeaveRequest $record): bool {
                $isEmployee = auth()->user()->isEmployee() && auth()->user()->employee->id == $record->employee->id;
                $isAlreadyCancelled = $record->status === LeaveRequestStatus::CANCELED;
                return $isEmployee && !$isAlreadyCancelled;
            })
            ->requiresConfirmation()
            ->action(function (LeaveRequest $record) {
                $record->update([
                    'status' => LeaveRequestStatus::CANCELED->value,
                ]);
                Notification::make()
                    ->title('Zahtjev uspješno otkazan')
                    ->warning()
                    ->send();
            });

    }
}
