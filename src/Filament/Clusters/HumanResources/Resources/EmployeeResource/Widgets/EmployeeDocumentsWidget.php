<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Actions\EmployeeAction;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;

class EmployeeDocumentsWidget extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected string $view = 'filament-employee-management::filament.clusters.human-resources.resources.employee-resource.widgets.employee-documents-widget';

    public ?Employee $record = null;

    public function connectToTelegramAction(): Action
    {
        return EmployeeAction::connectToTelegramAction($this->record);
    }
}
