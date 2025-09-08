<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource;
use Amicus\FilamentEmployeeManagement\Models\LeaveAllowance;
use Amicus\FilamentEmployeeManagement\Notifications\UserCredentialNotification;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $data = $this->form->getState();

        // If a password is provided, update the user's password.
        if (! empty($data['password']) && $this->record->user) {
            $this->record->user->update([
                'password' => Hash::make($data['password']),
            ]);
            $this->record->user->notify(new UserCredentialNotification($data['password']));
        }

        // If no user is associated, but email and password are provided, create a new user.
        if (! $this->record->user && empty($data['user_id']) && ! empty($data['password'])) {
            $user = User::create([
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $this->record->user_id = $user->id;

            // Send email notification
            $user->notify(new UserCredentialNotification($data['password']));
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $leaveAllowance = LeaveAllowance::where('employee_id', $this->record->id)
            ->where('year', now()->year)
            ->first();

        if ($leaveAllowance) {
            $data['total_days'] = $leaveAllowance->total_days;
            $data['notes'] = $leaveAllowance->notes;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();

        LeaveAllowance::updateOrCreate(
            [
                'employee_id' => $this->record->id,
                'year' => now()->year,
            ],
            [
                'total_days' => $data['total_days'],
                'valid_until_date' => now()->addYear()->month(6)->endOfMonth(),
                'notes' => $data['notes'],
            ]
        );
    }
}
