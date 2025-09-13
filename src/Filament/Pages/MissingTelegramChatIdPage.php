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

class MissingTelegramChatIdPage extends Page 
{

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

    public static function canAccess(): bool
    {
        return (auth()->user()->employee->telegram_chat_id == null);
    }
}
