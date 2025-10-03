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
            ->schema(fn($schema) => ProjectForm::configure($schema))
            ->label("Kreiraj projekt")
            ->modalHeading("Kreiraj projekt")
            ->successNotificationTitle("Projekt uspješno kreiran, možete ga pregledati u popisu.")
            ->action(function (array $data) {
                try{
                    Project::query()->create($data);
                }catch(\Exception $e){
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
            ->modalHeading("Novi projekt")
            ->slideOver()
            ->fillForm(fn() => [
                'status' => $status,
                'client_id' => $clientId,
            ])
            ->schema(fn($schema) => ProjectForm::configure($schema))
            ->action(function($data){
                try {
                    Project::create($data);
                    Notification::make()
                        ->title('Projekt uspješno kreiran')
                        ->success()
                        ->send();
                } catch(\Exception $e) {
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
            ->label('Izjava ovlaštenog projektanta')
            ->icon('heroicon-o-document-text')
            ->color('primary')

            ->action(function (Project $record) {
                $timestamp = now()->format('y-m-d-h-i');
                $fileName = "izjava_projektant_{$timestamp}.docx";
                $projectDirectory = "/projekti/{$record->id}";

                $data = [
                    'naziv_objekta' => $record->object->name,

                    'investitor_naziv' => $record->investitor->name,
                    'investitor_adresa' => $record->investitor->address,
                    'investitor_zip_code' => $record->investitor->zip_code,
                    'investitor_oib' => $record->investitor->oib,

                    "objekt_adresa"=>$record->object->address,
                    "danasnji_datum"=>now()->format('d.m.Y.'),

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
}
