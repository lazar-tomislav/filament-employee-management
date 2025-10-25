<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Tables\Columns;

use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Concerns\CanBeValidated;
use Filament\Tables\Columns\Concerns\CanUpdateState;
use Filament\Tables\Columns\Contracts\Editable;

class StatusSelectColumn extends Column implements Editable
{
    use CanBeValidated;
    use CanUpdateState;

    protected string $view = 'filament-employee-management::filament.tables.columns.status-select-column';

    public function enum(string $enumClass): static
    {
        $this->viewData(['enumClass' => $enumClass]);

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateUpdated(function (mixed $state) {
            $this->getLivewire()->dispatch('status-updated');
        });
    }
}
