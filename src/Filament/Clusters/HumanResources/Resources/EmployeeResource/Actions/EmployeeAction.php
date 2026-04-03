<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Actions;

use Amicus\FilamentEmployeeManagement\Enums\CroatianMonth;
use Amicus\FilamentEmployeeManagement\Exports\AllEmployeTimeReportExport;
use Amicus\FilamentEmployeeManagement\Exports\EmployeeReportExport;
use Amicus\FilamentEmployeeManagement\Exports\EmployeeReportTemplateExport;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas\EmployeeForm;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeAction
{
    public static function allEmployeTimeReportExport(): Action
    {
        return Action::make('export')
            ->label('Izvještaj radnih sati svih zaposlenika')
            ->icon(Heroicon::OutlinedDocumentArrowDown)
            ->visible(fn () => auth()->user()->isAdmin())
            ->color('primary')
            ->schema(fn ($schema) => EmployeeForm::monthlyTimeReport($schema))
            ->action(function (array $data) {
                return Excel::download(new AllEmployeTimeReportExport($data['month'], $data['year']), 'izvjestaj-svi-zaposlenici.xlsx');
            });
    }

    public static function allEmployeeMonthlyReportsZipExport(): Action
    {
        return Action::make('exportZip')
            ->label('Mjesečni izvještaji svih zaposlenika (ZIP)')
            ->icon(Heroicon::OutlinedArchiveBox)
            ->visible(fn () => auth()->user()->isAdmin())
            ->color('primary')
            ->schema(fn ($schema) => EmployeeForm::monthlyTimeReport($schema))
            ->action(function (array $data) {
                $month = $data['month'];
                $year = $data['year'];

                set_time_limit(600);
                ini_set('memory_limit', '1G');

                $employees = Employee::where('is_active', true)->get();

                $tempDir = storage_path('app/temp');
                if (! is_dir($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                $zipPath = $tempDir . '/' . uniqid('zip_', true) . '.zip';

                $zip = new \ZipArchive;
                if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
                    throw new \Exception('Ne mogu kreirati ZIP datoteku.');
                }

                foreach ($employees as $employee) {
                    $fileName = sprintf(
                        '%s-%s-%s-%s.xlsx',
                        strtolower(str_replace(' ', '-', $employee->first_name)),
                        strtolower(str_replace(' ', '-', $employee->last_name)),
                        strtolower(CroatianMonth::from($month)->asciiLabel()),
                        $year
                    );

                    try {
                        $export = new EmployeeReportTemplateExport($employee->id, $month, $year);
                        $tempFile = $export->generateFile($export->getTemplatePath());

                        $zip->addFromString($fileName, file_get_contents($tempFile));
                        @unlink($tempFile);
                    } catch (\Exception $e) {
                        report($e);
                    }
                }

                $zip->close();

                if (! file_exists($zipPath)) {
                    \Filament\Notifications\Notification::make()
                        ->title('Greška prilikom generiranja ZIP datoteke')
                        ->body('Nije bilo moguće generirati izvještaje.')
                        ->danger()
                        ->send();

                    return;
                }

                $zipFileName = sprintf(
                    'mjesecni-izvjestaji-%s-%s.zip',
                    strtolower(CroatianMonth::from($month)->asciiLabel()),
                    $year
                );

                return response()->download($zipPath, $zipFileName, [
                    'Content-Type' => 'application/zip',
                ])->deleteFileAfterSend(true);
            })
            ->modalSubmitActionLabel('Preuzmi ZIP')
            ->modalCancelActionLabel('Odustani');
    }

    public static function editEmployee(Employee $record): EditAction
    {
        return EditAction::make('edit')
            ->label('Uredi podatke zaposlenika')
            ->icon(Heroicon::OutlinedPencil)
            ->url(fn () => EmployeeResource::getUrl('edit', ['record' => $record]));
    }

    public static function requestLeave(Employee $record): Action
    {
        // simple button with link to "https://ink.test/admin/human-resources/employees/1?tab=absence"
        return Action::make('requestLeave')
            ->label('Zatraži godišnji odmor')
            ->icon(Heroicon::OutlinedCalendarDays)
            ->visible(fn () => auth()->user()->isEmployee() && auth()->user()->employee->id === $record->id)
            ->color('')
            ->url(fn () => EmployeeResource::getUrl('view', ['record' => $record, 'tab' => 'absence']));
    }

    public static function downloadMonthlyTimeReportAction(Employee $record): Action
    {
        return Action::make('downloadMonthlyTimeReport')
            ->label('Mjesečni izvještaj radnih sati')
            ->icon(Heroicon::OutlinedDocumentArrowDown)
            ->color('')
            ->schema(function () {
                $schema = [];

                // Only add template_type selector if template-based export is enabled
                // When disabled, we use the standard generated export (EmployeeReportExport)
                if (config('employee-management.monthly_report.enable_autogenerated', false)) {
                    $schema[] = Select::make('template_type')
                        ->label('Tip predloška')
                        ->options([
                            'generated' => 'Generirani Excel izvještaj',
                            'ods_template' => 'Službeni predložak (evidencija radnog vremena)',
                        ])
                        ->default('ods_template')
                        ->required()
                        ->helperText('Odaberite format izvještaja koji želite preuzeti.');
                }

                $schema[] = Select::make('month')
                    ->label('Mjesec')
                    ->options([
                        1 => '1. Siječanj',
                        2 => '2. Veljača',
                        3 => '3. Ožujak',
                        4 => '4. Travanj',
                        5 => '5. Svibanj',
                        6 => '6. Lipanj',
                        7 => '7. Srpanj',
                        8 => '8. Kolovoz',
                        9 => '9. Rujan',
                        10 => '10. Listopad',
                        11 => '11. Studeni',
                        12 => '12. Prosinac',
                    ])
                    ->searchable()
                    ->selectablePlaceholder(false)
                    ->preload()
                    ->default(now()->month)
                    ->required();

                $schema[] = Select::make('year')
                    ->label('Godina')
                    ->options(collect(range(now()->year - 2, now()->year + 1))
                        ->mapWithKeys(fn ($year) => [$year => $year]))
                    ->searchable()
                    ->selectablePlaceholder(false)
                    ->preload()
                    ->default(now()->year)
                    ->required();

                return $schema;
            })
            ->action(function (array $data) use ($record) {
                try {
                    // If enable_autogenerated is disabled, template_type field won't be in schema,
                    // so default to 'ods_template' (uses configured Excel template from .env)
                    $templateType = $data['template_type'] ?? 'ods_template';
                    $month = $data['month'];
                    $year = $data['year'];

                    $fileName = sprintf(
                        'izvjestaj-radnih-sati-%s-%s-%s.xlsx',
                        strtolower(str_replace(' ', '-', $record->full_name)),
                        strtolower(CroatianMonth::from($month)->asciiLabel()),
                        $year
                    );

                    // Choose export class based on template type
                    if ($templateType === 'ods_template') {
                        try {
                            $export = new EmployeeReportTemplateExport(
                                $record->id,
                                $month,
                                $year
                            );

                            return $export->download($fileName);
                        } catch (\Exception $e) {
                            // Fallback na generirani izvještaj ako template ne postoji
                            report($e);
                        }
                    }

                    $export = new EmployeeReportExport(
                        $record->id,
                        $month,
                        $year
                    );

                    return Excel::download($export, $fileName);
                } catch (\Exception $e) {
                    report($e);
                    \Filament\Notifications\Notification::make()
                        ->title('Greška prilikom generiranja izvještaja')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->modalSubmitActionLabel('Preuzmi izvještaj')
            ->modalCancelActionLabel('Odustani');
    }

    public static function connectToTelegramAction(Employee $employee): Action
    {
        return Action::make('connectToTelegram')
            ->label('Spoji s telegramom')
            ->color('')
            ->visible(fn () => config('employee-management.telegram-bot-api.is_active'))
            ->icon(Heroicon::OutlinedEnvelope)
            ->action(function () use ($employee) {
                $employee->update([
                    'telegram_denied_at' => null,
                    'telegram_chat_id' => null,
                ]);

                return redirect()->to(Dashboard::getUrl());
            });
    }
}
