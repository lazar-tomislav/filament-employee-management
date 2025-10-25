<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class ProjectPhaseFilter extends Field
{
    protected string $view = 'filament-employee-management::filament.forms.components.project-phase-filter';

    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'project_phases');
    }
}
