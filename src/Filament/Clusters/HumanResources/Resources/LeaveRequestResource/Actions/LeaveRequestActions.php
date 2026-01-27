<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Actions;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Services\LeaveRequestPdfService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
                    ->body('Zaposlenik je obaviješten o promjeni statusa. PDF je generiran.')
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
            ->modal()->modalWidth(\Filament\Support\Enums\Width::FiveExtraLarge)
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

    public static function editNotesAction(): Action
    {
        return \Filament\Actions\Action::make('edit_notes')
            ->label('Napomena')
            ->icon(Heroicon::OutlinedPencil)
            ->visible(fn ($record) => auth()->user()->isUredAdministrativnoOsoblje())
            ->fillForm(fn (LeaveRequest $record): array => [
                'notes' => $record->notes,
            ])
            ->schema([
                Textarea::make('notes')
                    ->label('Napomena')
                    ->maxLength(255)
                    ->rows(3),
            ])
            ->action(function (LeaveRequest $record, array $data) {
                $record->update([
                    'notes' => $data['notes'],
                ]);
                Notification::make()
                    ->title('Napomena spremljena')
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

    public static function downloadPdfAction(): Action
    {
        return Action::make('download_pdf')
            ->label('Skini PDF')
            ->icon(Heroicon::OutlinedDocumentArrowDown)
            ->visible(fn ($record) => $record->status === LeaveRequestStatus::APPROVED)
            ->action(function (LeaveRequest $record) {
                try {
                    // Generate PDF if not exists
                    if (! $record->pdf_path || ! Storage::disk('local')->exists($record->pdf_path)) {
                        $pdfPath = LeaveRequestPdfService::generatePdf($record);
                        if (! $pdfPath) {
                            Notification::make()
                                ->title('Greška')
                                ->body('PDF se nije mogao generirati. Pokušajte ponovno.')
                                ->danger()
                                ->send();

                            return;
                        }
                        $record->updateQuietly(['pdf_path' => $pdfPath]);
                    }

                    if ($record->pdf_path && Storage::disk('local')->exists($record->pdf_path)) {
                        $file = Storage::disk('local')->get($record->pdf_path);
                        $filename = basename($record->pdf_path);

                        return response()->streamDownload(function () use ($file) {
                            echo $file;
                        }, $filename, ['Content-Type' => 'application/pdf']);
                    } else {
                        Notification::make()
                            ->title('Greška')
                            ->body('PDF datoteka nije pronađena.')
                            ->danger()
                            ->send();
                    }
                } catch (\Exception $e) {
                    Log::error('PDF download failed: '.$e->getMessage());
                    Notification::make()
                        ->title('Greška')
                        ->body('Došlo je do greške prilikom preuzimanja PDF-a.')
                        ->danger()
                        ->send();
                }
            });
    }
}
