<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeaveRequest extends EditRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Pregled'),
            Actions\DeleteAction::make()
                ->label('Obriši'),
            Actions\ForceDeleteAction::make()
                ->label('Trajno obriši'),
            Actions\RestoreAction::make()
                ->label('Vrati'),
        ];
    }

    public function getTitle(): string
    {
        return 'Uredi zahtjev';
    }
}
