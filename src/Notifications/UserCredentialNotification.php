<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Mail\UserCredentialMail;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UserCredentialNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $password
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): UserCredentialMail
    {
        return new UserCredentialMail($notifiable->email, $this->password);
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Nova lozinka je poslana')
            ->body('Kreirana je nova lozinka za vaš korisnički račun. Provjerite email.')
            ->getDatabaseMessage();
    }
}
