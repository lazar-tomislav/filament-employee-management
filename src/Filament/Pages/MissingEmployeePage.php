<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas\EmployeeForm;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Observers\EmployeeObserver;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Illuminate\Support\Facades\Log;

class MissingEmployeePage extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;
    protected string $view = 'filament-employee-management::filament.pages.missing-employee-page';

    protected static ?string $title = "Kreiraj svoj profil zaposlenika";

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        // Redirect if user already has employee record
        if(auth()->user()->isEmployee()){
            redirect()->to(Dashboard::getUrl());
        }

        $this->form->fill([
            "user_id" => auth()->id(),
            'first_name' => auth()->user()->name,
            "email" => auth()->user()->email,
            "is_active" => true,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema)
            ->statePath('data')
            ->model(Employee::class);
    }

    public function create(): void
    {
        try{
            $data = $this->form->getState();
            $user = User::find(auth()->id());
            if(!$user){
                throw new \Exception('Odabrani korisnik ne postoji.');
            }
            // Create employee record linked to selected user
            $employee = Employee::query()->createQuietly([
                'user_id' => $user->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'],
                'address' => $data['address'],
                'city' => $data['city'],
                'oib' => $data['oib'],
                'mobile_tariff' => $data['mobile_tariff'] ?? null,
                'note' => null,
                'is_active' => true,
            ]);

            EmployeeObserver::onCreatedEvent($employee);

            Notification::make()
                ->title('Profil uspješno ažuriran. Možete nastaviti s radom.')
                ->success()
                ->send();

            redirect()->to(Dashboard::getUrl());

        }catch(\Exception $e){
            report($e);
            Log::error('Greška prilikom kreiranja profila zaposlenika: ' . $e->getMessage());
            Notification::make()
                ->title('Greška prilikom kreiranja profila')
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Kreiraj svoj profil')
                ->submit('create')
                ->color('primary'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->check() && !auth()->user()->isEmployee();
    }
}
