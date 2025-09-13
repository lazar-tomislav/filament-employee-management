<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas\EmployeeForm;
use Amicus\FilamentEmployeeManagement\Models\Employee;
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
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Get;
use BackedEnum;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
        return $schema
            ->components([
            ...EmployeeForm::getTelegramChatIdFields(),

        ]);
    }

    public function getChatId():Action
    {

        return Action::make("get_chat_id")
            ->label("Dohvati Telegram Chat ID")
            ->action(function () {
             // send request to Route::get('/test-telegram', function() {
                //    $response = file_get_contents('https://api.telegram.org/bot8207642879:AAFxrx9dlbFp1JQA7aT8mV4sY1ATgalMwjQ/getUpdates');
                //    $data = json_decode($response, true);
                //    return response()->json($data);
                //});
                // and fetch the chat_id from the response //{
                //"ok": true,
                //"result": [
                //{
                //"update_id": 60811634,
                //"message": {
                //"message_id": 2,
                //"from": {
                //"id": 6340211198,
                //"is_bot": false,
                //"first_name": "Tomislav",
                //"last_name": "Lazar",
                //"username": "lazartomislav",
                //"language_code": "en"
                //},
                //"chat": {
                //"id": 6340211198,
                //"first_name": "Tomislav",
                //"last_name": "Lazar",
                //"username": "lazartomislav",
                //"type": "private"
                //},
                //"date": 1757764995,
                //"text": "/start",
                //"entities": [
                //{
                //"offset": 0,
                //"length": 6,
                //"type": "bot_command"
                //}
                //]
                //}
                //},
                //{
                //"update_id": 60811635,
                //"message": {
                //"message_id": 3,
                //"from": {
                //"id": 6340211198,
                //"is_bot": false,
                //"first_name": "Tomislav",
                //"last_name": "Lazar",
                //"username": "lazartomislav",
                //"language_code": "en"
                //},
                //"chat": {
                //"id": 6340211198,
                //"first_name": "Tomislav",
                //"last_name": "Lazar",
                //"username": "lazartomislav",
                //"type": "private"
                //},
                //"date": 1757765109,
                //"text": "test"
                //}
                //},
                //{
                //"update_id": 60811636,
                //"message": {
                //"message_id": 4,
                //"from": {
                //"id": 6340211198,
                //"is_bot": false,
                //"first_name": "Tomislav",
                //"last_name": "Lazar",
                //"username": "lazartomislav",
                //"language_code": "en"
                //},
                //"chat": {
                //"id": 6340211198,
                //"first_name": "Tomislav",
                //"last_name": "Lazar",
                //"username": "lazartomislav",
                //"type": "private"
                //},
                //"date": 1757765253,
                //"text": "Tesssstttt"
                //}
                //},
                //{
                //"update_id": 60811637,
                //"message": {
                //"message_id": 5,
                //"from": {
                //"id": 6340211198,
                //"is_bot": false,
                //"first_name": "Tomislav",
                //"last_name": "Lazar",
                //"username": "lazartomislav",
                //"language_code": "en"
                //},
                //"chat": {
                //"id": 6340211198,
                //"first_name": "Tomislav",
                //"last_name": "Lazar",
                //"username": "lazartomislav",
                //"type": "private"
                //},
                //"date": 1757767348,
                //"text": "/start",
                //"entities": [
                //{
                //"offset": 0,
                //"length": 6,
                //"type": "bot_command"
                //}
                //]
                //}
                //}
                //]
                //}
            })
            ->color('primary');
    }

    public function save(): void
    {
        try {
            $this->validate();

            $employee = auth()->user()->employee;
            $employee->update([
                'telegram_chat_id' => $this->data['telegram_chat_id']
            ]);

            Notification::make()
                ->title('Telegram Chat ID je uspješno postavljen!')
                ->success()
                ->send();

            redirect()->to(Dashboard::getUrl());

        } catch (ValidationException $e) {
            foreach ($e->validator->errors()->all() as $error) {
                Notification::make()
                    ->title('Greška pri validaciji')
                    ->body($error)
                    ->danger()
                    ->send();
            }
            throw $e;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Dogodila se greška')
                ->body('Molimo pokušajte ponovo ili kontaktirajte administratora.')
                ->danger()
                ->send();

            Log::error('Error saving telegram_chat_id', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public static function canAccess(): bool
    {
        return (auth()->user()->employee->telegram_chat_id == null);
    }
}
