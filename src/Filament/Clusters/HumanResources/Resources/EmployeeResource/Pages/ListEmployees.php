<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->isEmployee()) {
            return;
        }

        if ($user->canSeeAllLeave() || $user->hodDepartmentIds()->isNotEmpty()) {
            return;
        }

        if ($user->employee) {
            redirect()->to(EmployeeResource::getUrl('view', ['record' => $user->employee->id]));
        }
    }

    public function getHeading(): string|Htmlable
    {
        return 'Zaposlenici';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
