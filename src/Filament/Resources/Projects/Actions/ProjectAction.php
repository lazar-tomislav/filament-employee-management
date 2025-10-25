<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Actions;

use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Schemas\ProjectForm;
use Amicus\FilamentEmployeeManagement\Models\Project;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;

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
}
