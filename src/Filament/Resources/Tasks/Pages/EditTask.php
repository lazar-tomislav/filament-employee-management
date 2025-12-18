<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\TaskResource;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}