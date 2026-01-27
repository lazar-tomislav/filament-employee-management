<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Actions;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Notifications\LeaveRequestReminderNotification;
use Amicus\FilamentEmployeeManagement\Services\LeaveRequestPdfService;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LeaveRequestActions
{
    public static function approveAsHeadOfDepartmentAction(): Action
    {
        return Action::make('approve_as_hod')
            ->icon(Heroicon::OutlinedCheck)
            ->label('Odobri kao voditelj')
            ->color('success')
            ->visible(function (LeaveRequest $record): bool {
                $employee = auth()->user()->employee ?? null;

                if (! $employee) {
                    return false;
                }

                return $record->canBeApprovedByHeadOfDepartment($employee);
            })
            ->requiresConfirmation()
            ->modalHeading('Odobri zahtjev kao voditelj odjela')
            ->modalDescription(function (LeaveRequest $record): string {
                return "Odobrit ćete zahtjev zaposlenika {$record->employee->full_name_email} za odsustvo u periodu od {$record->start_date->format('d.m.Y')} do {$record->end_date->format('d.m.Y')}.\n\nNakon vašeg odobrenja, zahtjev će biti proslijeđen direktoru na finalno odobrenje.";
            })
            ->action(function (LeaveRequest $record): void {
                $employee = auth()->user()->employee;

                $record->update([
                    'approved_by_head_of_department_id' => $employee->id,
                    'approved_by_head_of_department_at' => now(),
                ]);

                Notification::make()
                    ->title('Zahtjev odobren')
                    ->body('Zahtjev je proslijeđen direktoru na finalno odobrenje.')
                    ->success()
                    ->send();
            });
    }

    public static function approveAsDirectorAction(): Action
    {
        return Action::make('approve_as_director')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->label('Finalno odobri')
            ->color('success')
            ->visible(function (LeaveRequest $record): bool {
                $employee = auth()->user()->employee ?? null;

                if (! $employee) {
                    return false;
                }

                return $record->canBeApprovedByDirector($employee);
            })
            ->requiresConfirmation()
            ->modalHeading('Finalno odobrenje zahtjeva')
            ->modalDescription(function (LeaveRequest $record): string {
                $text = "Finalno ćete odobriti zahtjev zaposlenika {$record->employee->full_name_email} za odsustvo u periodu od {$record->start_date->format('d.m.Y')} do {$record->end_date->format('d.m.Y')}.";

                if ($record->isApprovedByHeadOfDepartment()) {
                    $text .= "\n\nVoditelj odjela ({$record->headOfDepartmentApprover->full_name}) je već odobrio ovaj zahtjev.";
                }

                return $text;
            })
            ->action(function (LeaveRequest $record): void {
                $employee = auth()->user()->employee;

                $record->update([
                    'approved_by_director_id' => $employee->id,
                    'approved_by_director_at' => now(),
                    'status' => LeaveRequestStatus::APPROVED->value,
                ]);

                Notification::make()
                    ->title('Zahtjev odobren')
                    ->body('Zaposlenik je obaviješten o promjeni statusa. PDF je generiran.')
                    ->success()
                    ->send();
            });
    }

    public static function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('Odbij zahtjev')
            ->icon(Heroicon::OutlinedXCircle)
            ->visible(function (LeaveRequest $record): bool {
                if ($record->status !== LeaveRequestStatus::PENDING) {
                    return false;
                }

                $employee = auth()->user()->employee ?? null;

                if (! $employee) {
                    return false;
                }

                $settings = app(HumanResourcesSettings::class);

                return $employee->id === $settings->employee_director_id;
            })
            ->color('danger')
            ->schema([
                Textarea::make('rejection_reason')
                    ->label('Razlog odbijanja')
                    ->helperText('Zaposlenik će primiti obavijest o odbijanju zahtjeva s razlogom.')
                    ->required(),
            ])
            ->modal()->modalWidth(\Filament\Support\Enums\Width::FiveExtraLarge)
            ->action(function (LeaveRequest $record, array $data): void {
                $employee = auth()->user()->employee;

                $record->update([
                    'status' => LeaveRequestStatus::REJECTED->value,
                    'approved_by_director_id' => $employee->id,
                    'approved_by_director_at' => now(),
                    'rejection_reason' => $data['rejection_reason'],
                ]);

                Notification::make()
                    ->title('Zahtjev odbijen')
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

                return $isEmployee && ! $isAlreadyCancelled;
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

    public static function sendReminderAction(): Action
    {
        return Action::make('send_reminder')
            ->label('Podsjeti na odobrenje')
            ->icon(Heroicon::OutlinedBellAlert)
            ->color('warning')
            ->visible(function (LeaveRequest $record): bool {
                if ($record->status !== LeaveRequestStatus::PENDING) {
                    return false;
                }

                $user = auth()->user();

                if (! $user || ! $user->employee) {
                    return false;
                }

                return $user->employee->id === $record->employee_id;
            })
            ->requiresConfirmation()
            ->modalHeading('Podsjeti na odobrenje')
            ->modalDescription(function (LeaveRequest $record): string {
                if ($record->requiresHeadOfDepartmentApproval() && ! $record->isApprovedByHeadOfDepartment()) {
                    $hodName = $record->employee->department?->headOfDepartment?->full_name ?? 'voditelj odjela';

                    return "Poslat ćete podsjetnik voditelju odjela ({$hodName}) za odobrenje vašeg zahtjeva.";
                }

                $settings = app(HumanResourcesSettings::class);
                $director = Employee::find($settings->employee_director_id);
                $directorName = $director?->full_name ?? 'direktor';

                return "Poslat ćete podsjetnik direktoru ({$directorName}) za finalno odobrenje vašeg zahtjeva.";
            })
            ->action(function (LeaveRequest $record): void {
                $settings = app(HumanResourcesSettings::class);

                if ($record->requiresHeadOfDepartmentApproval() && ! $record->isApprovedByHeadOfDepartment()) {
                    $headOfDepartment = $record->employee->department?->headOfDepartment;

                    if ($headOfDepartment?->user) {
                        $headOfDepartment->user->notify(new LeaveRequestReminderNotification($record));

                        Notification::make()
                            ->title('Podsjetnik poslan')
                            ->body("Voditelj odjela ({$headOfDepartment->full_name}) je obaviješten.")
                            ->success()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Greška')
                        ->body('Voditelj odjela nije pronađen.')
                        ->danger()
                        ->send();

                    return;
                }

                $director = Employee::find($settings->employee_director_id);

                if ($director?->user) {
                    $director->user->notify(new LeaveRequestReminderNotification($record));

                    Notification::make()
                        ->title('Podsjetnik poslan')
                        ->body("Direktor ({$director->full_name}) je obaviješten.")
                        ->success()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Greška')
                    ->body('Direktor nije konfiguriran u postavkama.')
                    ->danger()
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
                    Log::error('PDF download failed: ' . $e->getMessage());
                    Notification::make()
                        ->title('Greška')
                        ->body('Došlo je do greške prilikom preuzimanja PDF-a.')
                        ->danger()
                        ->send();
                }
            });
    }
}
