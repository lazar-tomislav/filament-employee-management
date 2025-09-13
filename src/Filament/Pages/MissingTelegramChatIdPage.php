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
        // Redirect if user already has employee record or if they have denied telegram_denied_at
        if(auth()->user()->employee->telegram_chat_id || auth()->user()->employee->telegram_denied_at){
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

    public function skipAction(): Action
    {
        return Action::make('skip')
            ->color('secondary')
            ->label("Preskoči")
            ->action(function () {
                auth()->user()->employee()->update([
                    'telegram_denied_at' => now()
                ]);

                redirect()->to(Dashboard::getUrl());
            });
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->color('primary')
            ->label("Dohvati Telegram Chat ID")
            ->modalHeading("Odaberite svoj korisničko ime")
            ->modalDescription("Odaberite svoje korisničko ime iz popisa korisnika koji su nedavno poslali poruku botu:")
            ->schema([
                \Filament\Forms\Components\Select::make('selected_chat_id')
                    ->label('Korisničko ime')
                    ->options(function () {
                        try{
                            $botToken = config('services.telegram-bot-api.token');
                            $telegramApi = "https://api.telegram.org/bot{$botToken}/getUpdates";

                            $response = file_get_contents($telegramApi);

                            if($response === false){
                                return [];
                            }

                            $data = json_decode($response, true);
                            if(!$data || !$data['ok']){
                                return [];
                            }

                            $results = $data['result'] ?? [];

                            if(empty($results)){
                                return [];
                            }

                            $users = [];
                            // Get last 10 messages to show recent users
                            $recentMessages = array_slice($results, -10);

                            foreach($recentMessages as $message){
                                $chatId = $message['message']['chat']['id'] ?? null;
                                $userName = $message['message']['from']['first_name'] ?? '';
                                $userLastName = $message['message']['from']['last_name'] ?? '';
                                $username = $message['message']['from']['username'] ?? null;

                                if($chatId){
                                    $displayName = trim($userName . ' ' . $userLastName);
                                    if($username){
                                        $displayName .= " (@{$username})";
                                    }

                                    $users[$chatId] = $displayName;
                                }
                            }

                            return array_reverse($users, true); // Show newest first
                        }catch(\Exception $e){
                            return [];
                        }
                    })
                    ->required()
                    ->placeholder('Odaberite svoje ime iz popisa')
                    ->native(true)
            ])
            ->action(function (array $data) {
                try{
                    $selectedChatId = $data['selected_chat_id'];

                    if(!$selectedChatId){
                        throw new \Exception('Chat ID not selected');
                    }

                    $this->data['telegram_chat_id'] = $selectedChatId;
                    $this->form->fill($this->data);

                    auth()->user()->employee()->update([
                        'telegram_chat_id' => $selectedChatId
                    ]);

                    Notification::make()
                        ->title('Chat ID uspješno postavljen!')
                        ->success()
                        ->send();

                    redirect()->to(Dashboard::getUrl());

                }catch(\Exception $e){
                    report($e);
                    Notification::make()
                        ->title('Greška pri dohvaćanju Chat ID-a')
                        ->body('Molimo pokušajte ponovo ili kontaktirajte administratora.')
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
