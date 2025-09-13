<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Actions\EmployeeAction;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Dashboard;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;

class EmployeeDocumentsWidget extends Widget implements HasActions,HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected string $view = 'filament-employee-management::filament.clusters.human-resources.resources.employee-resource.widgets.employee-documents-widget';

    public ?Employee $record = null;

    public function downloadMonthlyTimeReportAction():Action
    {
        return EmployeeAction::downloadMonthlyTimeReportAction($this->record);
    }

    public function connectToTelegramAction(): Action
    {
        return Action::make('connectToTelegram')
            ->label('Spoji s telegramom')
            ->color('')
            ->icon(Heroicon::OutlinedEnvelope)
            ->action(function () {
                $this->record->update([
                    'telegram_denied_at' => null,
                    'telegram_chat_id' => null
                ]);
                $this->redirect(Dashboard::getUrl());
            });
    }
}
