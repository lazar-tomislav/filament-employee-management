<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Actions;

use Amicus\FilamentEmployeeManagement\Exports\AllEmployeTimeReportExport;
use Amicus\FilamentEmployeeManagement\Exports\EmployeeReportExport;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas\EmployeeForm;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Dashboard;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Google\Service\Books\Notification;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeAction
{

    public static function allEmployeTimeReportExport(): Action
    {
        return Action::make('export')
            ->label('Izvještaj radnih sati svih zaposlenika')
            ->icon(Heroicon::OutlinedDocumentArrowDown)
            ->visible(fn() => auth()->user()->isUredAdministrativnoOsoblje())
            ->color("")
            ->schema([
                Select::make('month')
                    ->label('Mjesec')
                    ->options([
                        1 => 'Siječanj',
                        2 => 'Veljača',
                        3 => 'Ožujak',
                        4 => 'Travanj',
                        5 => 'Svibanj',
                        6 => 'Lipanj',
                        7 => 'Srpanj',
                        8 => 'Kolovoz',
                        9 => 'Rujan',
                        10 => 'Listopad',
                        11 => 'Studeni',
                        12 => 'Prosinac',
                    ])
                    ->default(now()->subMonth()->month)
                    ->required(),
                TextInput::make('year')
                    ->label('Godina')
                    ->numeric()
                    ->default(now()->year)
                    ->required(),
            ])
            ->action(function (array $data) {
                return Excel::download(new AllEmployeTimeReportExport($data['month'], $data['year']), 'izvjestaj-svi-zaposlenici.xlsx');
            });
    }

    public static function editEmployee(Employee $record): Action
    {
        return Action::make("edit")
            ->label("Uredi podatke zaposlenika")
            ->slideOver()
            ->color("")
            ->modalHeading('Uredi zaposlenika')
            ->icon(Heroicon::OutlinedPencil)
            ->fillForm($record->toArray())
            ->schema(fn(Schema $schema) => EmployeeResource\Schemas\EmployeeForm::configure($schema)->record($record))
            ->action(function (array $data) use ($record) {
                try{
                    $record->update($data);
                    \Filament\Notifications\Notification::make()
                        ->body('Podaci zaposlenika su uspješno ažurirani.')
                        ->success()
                        ->send();
                }catch(\Exception $e){
                    report($e);
                    \Filament\Notifications\Notification::make()
                        ->title('Greška prilikom ažuriranja zaposlenika')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function requestLeave(Employee $record): Action
    {
        // simple button with link to "https://ink.test/admin/human-resources/employees/1?tab=absence"
        return Action::make('requestLeave')
            ->label('Zatraži godišnji odmor')
            ->icon(Heroicon::OutlinedCalendarDays)
            ->visible(fn() => auth()->user()->isEmployee() && auth()->user()->employee->id === $record->id)
            ->color('')
            ->url(fn() => EmployeeResource::getUrl('view', ['record' => $record, 'tab' => 'absence']));
    }

    public static function downloadMonthlyTimeReportAction(Employee $record): Action
    {
        return Action::make('downloadMonthlyTimeReport')
            ->label('Mjesečni izvještaj radnih sati')
            ->icon(Heroicon::OutlinedDocumentArrowDown)
            ->color('')
            ->schema(fn($schema) => EmployeeForm::monthlyTimeReport($schema))
            ->action(function (array $data) use ($record) {
                $export = new EmployeeReportExport(
                    $record->id,
                    $data['month'],
                    $data['year']
                );
                $monthNames = [
                    1 => 'Siječanj', 2 => 'Veljača', 3 => 'Ožujak', 4 => 'Travanj',
                    5 => 'Svibanj', 6 => 'Lipanj', 7 => 'Srpanj', 8 => 'Kolovoz',
                    9 => 'Rujan', 10 => 'Listopad', 11 => 'Studeni', 12 => 'Prosinac'
                ];

                $fileName = sprintf(
                    'izvjestaj-radnih-sati-%s-%s-%s.xlsx',
                    strtolower(str_replace(' ', '-', $record->full_name)),
                    strtolower($monthNames[$data['month']]),
                    $data['year']
                );

                return Excel::download($export, $fileName);
            })
            ->modalSubmitActionLabel('Preuzmi izvještaj')
            ->modalCancelActionLabel('Odustani');
    }

    public static function connectToTelegramAction(Employee $employee): Action
    {
        return Action::make('connectToTelegram')
            ->label('Spoji s telegramom')
            ->color('')
            ->icon(Heroicon::OutlinedEnvelope)
            ->action(function () use ($employee) {
                $employee->update([
                    'telegram_denied_at' => null,
                    'telegram_chat_id' => null
                ]);
                return redirect()->to(Dashboard::getUrl());
            });
    }
}
