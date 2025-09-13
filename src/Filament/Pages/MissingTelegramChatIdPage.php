<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas\EmployeeForm;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\HasWizard;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
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

class MissingTelegramChatIdPage extends Page implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected string $view = 'filament-employee-management::filament.pages.missing-telegram-chat-id';

    protected static ?string $title = "Postavi telegram obavijesti";
    protected ?string $heading = '';
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        // Redirect if user already has employee record
        if(auth()->user()->employee->telegram_chat_id){
            redirect()->to(Dashboard::getUrl());
        }
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                EmployeeForm::getTelegramChatIdField()->extraInputAttributes([
                    'readonly' => true,
                ]),
            ]);
    }
    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->color('primary')
            ->label("Dohvati Telegram Chat ID")
            ->modalHeading("Jeste li poslali poruku botu?")
            ->modalDescription("Ako niste poslali poruku botu, molimo to učinite prije nego što nastavite jer u suprotnom nećete moći dohvatiti Chat ID.")
            ->requiresConfirmation()
            ->action(function () {
                try{
                    $botToken = config('services.telegram-bot-api.token');
                    $telegramApi = "https://api.telegram.org/bot{$botToken}/getUpdates";

                    $response = file_get_contents($telegramApi);

                    if($response === false){
                        throw new \Exception('Failed to connect to Telegram API');
                    }

                    $data = json_decode($response, true);
                    if(!$data || !$data['ok']){
                        throw new \Exception('Invalid response from Telegram API');
                    }

                    $results = $data['result'] ?? [];

                    if(empty($results)){
                        Notification::make()
                            ->title('Nema novih poruka')
                            ->body('Molimo pošaljite bilo koju poruku botu na Telegramu pa pokušajte ponovo.')
                            ->warning()
                            ->send();
                        return;
                    }

                    $latestMessage = end($results);
                    $chatId = $latestMessage['message']['chat']['id'] ?? null;
                    $userName = $latestMessage['message']['from']['first_name'] ?? '';
                    $userLastName = $latestMessage['message']['from']['last_name'] ?? '';

                    if(!$chatId){
                        throw new \Exception('Chat ID not found in the response');
                    }

                    $this->data['telegram_chat_id'] = $chatId;
                    $this->form->fill($this->data);

                    auth()->user()->employee()->update([
                        'telegram_chat_id' => $chatId
                    ]);

                    Notification::make()
                        ->title('Chat ID uspješno dohvaćen!')
                        ->body("Pronađen Chat ID za korisnika: {$userName} {$userLastName}: {$chatId}")
                        ->success()
                        ->send();

                    redirect()->to(Dashboard::getUrl());

                }catch(\Exception $e){
                    report($e);
                    Notification::make()
                        ->title('Greška pri dohvaćanju Chat ID-a')
                        ->danger()
                        ->send();
                }
            });
    }


    public function save(): void
    {
        try{
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

        }catch(ValidationException $e){
            foreach($e->validator->errors()->all() as $error){
                Notification::make()
                    ->title('Greška pri validaciji')
                    ->body($error)
                    ->danger()
                    ->send();
            }
            throw $e;
        }catch(\Exception $e){
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
