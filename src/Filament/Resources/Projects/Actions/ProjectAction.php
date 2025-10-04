<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Actions;

use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Schemas\ProjectForm;
use Amicus\FilamentEmployeeManagement\Models\Project;
use App\Classes\DocxTemplates;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class ProjectAction
{
    public static function createAction(): CreateAction
    {
        return CreateAction::make()
            ->slideOver()
            ->schema(fn ($schema) => ProjectForm::configure($schema))
            ->label('Kreiraj projekt')
            ->modalHeading('Kreiraj projekt')
            ->successNotificationTitle('Projekt uspješno kreiran, možete ga pregledati u popisu.')
            ->action(function (array $data) {
                try {
                    Project::query()->create($data);
                } catch (\Exception $e) {
                    report($e);
                    Notification::make()->title('Greška')->body('Neuspješno kreiranje projekta.')->danger()->send();
                }
            });
    }

    public static function quickCreateProject(StatusProjekta $status, ?int $clientId = null): Action
    {
        return Action::make('quick_create')
            ->icon(Heroicon::OutlinedPlus)
            ->hiddenLabel()
            ->modalHeading('Novi projekt')
            ->slideOver()
            ->fillForm(fn () => [
                'status' => $status,
                'client_id' => $clientId,
            ])
            ->schema(fn ($schema) => ProjectForm::configure($schema))
            ->action(function ($data) {
                try {
                    Project::create($data);
                    Notification::make()
                        ->title('Projekt uspješno kreiran')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    report($e);
                    Notification::make()
                        ->title('Greška')
                        ->body('Neuspješno kreiranje projekta.')
                        ->danger()
                        ->send();
                }
            });
    }

    public static function generateIzjavaProjektant(): Action
    {
        return Action::make('generateIzjavaProjektant')
            ->label('Izjava projektanta')
            ->icon('heroicon-o-document-text')
            ->color('primary')

            ->action(function (Project $record) {
                $timestamp = now()->format('y-m-d-h-i');
                $fileName = "izjava_projektant_{$timestamp}.docx";
                $projectDirectory = "/private/projekti/{$record->id}";

                $data = [
                    'naziv_objekta' => $record->object->name,

                    'investitor_naziv' => $record->investitor->name,
                    'investitor_adresa' => $record->investitor->address,
                    'investitor_zip_code' => $record->investitor->zip_code,
                    'investitor_oib' => $record->investitor->oib,

                    'objekt_adresa' => $record->object->address,
                    'danasnji_datum' => now()->format('d.m.Y.'),

                    'investitor_grad' => $record->investitor->grad,
                ];

                $outputPath = DocxTemplates::generate(
                    DocxTemplates::IZJAVA_PROJEKTANT,
                    $data,
                    $projectDirectory,
                    $fileName
                );

                if (! $outputPath) {
                    Notification::make()
                        ->title('Greška')
                        ->body('Template izjava_projektant.docx ne postoji.')
                        ->danger()
                        ->send();

                    return;
                }

                return response()->download($outputPath, $fileName);
            });
    }

    public static function generateZapisnikOPrimopredaji(): Action
    {
        return Action::make('generateZapisnikOPrimopredaji')
            ->label('Zapisnik o primopredaji')
            ->icon('heroicon-o-document-text')
            ->color('primary')
            ->action(function (Project $record) {
                $timestamp = now()->format('y-m-d-h-i');
                $fileName = "zapisnik-o-primopredaji-{$timestamp}.docx";
                $projectDirectory = "/private/projekti/{$record->id}";

                $data = [
                    'klijent_naziv' => $record->client->name,
                    'klijent_adresa' => $record->client->address,
                    'klijent_zip_code' => $record->client->zip_code,
                    'klijent_grad' => $record->client->grad,
                    'klijent_oib' => $record->client->oib,

                    'danasnji_datum' => now()->format('d.m.Y.'),
                ];

                $outputPath = DocxTemplates::generate(
                    DocxTemplates::PRIMOPREDAJNI_ZAPISNIK,
                    $data,
                    $projectDirectory,
                    $fileName
                );

                if (! $outputPath) {
                    Notification::make()
                        ->title('Greška')
                        ->body('Template izjava o primopredaji ne postoji.')
                        ->danger()
                        ->send();

                    return;
                }

                return response()->download($outputPath, $fileName);
            });
    }

    public static function generateImenovanjeVoditeljGradilista(): Action
    {
        return Action::make('generateImenovanjeVoditeljGradilista')
            ->label('Imenovanje voditelj gradilišta')
            ->icon('heroicon-o-document-text')
            ->color('primary')
            ->action(function (Project $record) {
                $timestamp = now()->format('y-m-d-h-i');
                $fileName = "imenovanje-voditelj-gradilista-{$timestamp}.docx";
                $projectDirectory = "/private/projekti/{$record->id}";

                $data = [
                    'broj_gradilista' => '01 / 2025',
                    'naziv_objekta' => $record->object->name,
                    'snaga_elektrane' => $record->power_plant_power ?? '',
                    'investitor_naziv' => $record->investitor->name,
                    'investitor_oib' => $record->investitor->oib,
                    'investitor_adresa' => $record->investitor->address,
                    'investitor_zip_code' => $record->investitor->zip_code,
                    'investitor_grad' => $record->investitor->grad,
                    'objekt_adresa' => $record->object->address,
                    'parcel_reference' => $record->object->parcel_reference ?? '',
                    'danasnji_datum' => now()->format('d.m.Y'),
                ];

                $outputPath = DocxTemplates::generate(
                    DocxTemplates::IMENOVANJE_VODITELJ_GRADILISTA,
                    $data,
                    $projectDirectory,
                    $fileName
                );

                if (! $outputPath) {
                    Notification::make()
                        ->title('Greška')
                        ->body('Template imenovanje-voditelj-gradilista.docx ne postoji.')
                        ->danger()
                        ->send();

                    return;
                }

                return response()->download($outputPath, $fileName);
            });
    }

    public static function generateGradilisteLista(): Action
    {
        return Action::make('generateGradilisteLista')
            ->label('Prazna špranca za gradilište')
            ->icon('heroicon-o-document-text')
            ->color('primary')
            ->url(route('gradiliste-lista.generate'))
            ->openUrlInNewTab();
    }

    public static function generateRobaGradiliste(): Action
    {
        return Action::make('generateRobaGradiliste')
            ->label('Roba za gradilište')
            ->icon('heroicon-o-document-text')
            ->color('primary')
            ->url(route('roba-gradiliste.generate'))
            ->openUrlInNewTab();
    }
}
