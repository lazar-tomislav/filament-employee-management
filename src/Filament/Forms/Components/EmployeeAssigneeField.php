<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class EmployeeAssigneeField extends Field
{
    protected string $view = 'filament-employee-management::filament.forms.components.employee-assignee-field';

    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'assignee_id');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->live()
            ->afterStateUpdated(function (mixed $state) {
                \Log::info('EmployeeAssigneeField afterStateUpdated called', ['state' => $state]);
                $this->getLivewire()->dispatch('filter-tasks', filters: ['assigneeId' => $state]);
            });

    }
}
