<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas\EmployeeForm;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Observers\EmployeeObserver;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\HasWizard;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Illuminate\Support\Facades\Log;

class MissingTelegramChatIdPage extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament-employee-management::filament.pages.missing-telegram-chat-id';

    protected static ?string $title = "Postavi telegram obavijesti";

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        // Redirect if user already has employee record
        if(auth()->user()->employee->telegram_chat_id !== null){
            redirect()->to(Dashboard::getUrl());
        }
    }

    public function form(Schema $schema):Schema
    {
        return $schema->components([
            Wizard::make()
                ->steps([
                    Step::make('Pošaljite poruku')
                        ->schema([
                            // objašnjenje gdje korisnik mora poslati poruku na telegram bot, to je link koji vodi na telegram bot
                            // https://t.me/net_eko_bot
                            // checkbox koji user mora kliknuti "poslao sam poruku" i zatim tek može kliknuti na "Sljedeći korak"
                        ])
                        ->columns(1),
                    Step::make('Unesite Telegram Chat ID')
                        ->schema([
                            ...EmployeeForm::getTelegramChatIdFields(),
                        ])
                        ->columns(1),
                ])
                ->submitAction(
                    Action::make('submit')
                        ->label('Spremi')
                        ->action(function () {
                        })
                )
        ]);
    }

    public static function canAccess(): bool
    {
        return (auth()->user()->employee->telegram_chat_id == null);
    }
}
