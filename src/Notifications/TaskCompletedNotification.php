<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Models\Task;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TaskCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task
    ) {
        logger("task completed notification created for task ID: {$task->id}");
    }

    public function via(object $notifiable): array
    {
        $isTelegramActive = config('employee-management.telegram-bot-api.is_active') && $notifiable->telegram_chat_id;

        if ($notifiable instanceof \Amicus\FilamentEmployeeManagement\Services\GeneralNotificationTarget) {
            return $isTelegramActive ? ['telegram'] : [];
        }

        $channels = ['database'];

        if ($isTelegramActive) {
            $channels[] = 'telegram';
        }

        return $channels;
    }

    public function toTelegram(object $notifiable): ?TelegramMessage
    {
        if (! config('employee-management.telegram-bot-api.is_active')) {
            return null;
        }

        if (! $notifiable->telegram_chat_id) {
            return null;
        }

        $message = TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("✅ <strong>Zadatak je završen!</strong>\n\n" .
                "<strong>{$this->task->title}</strong>\n\n" .
                ($this->task->project ? "Projekt: {$this->task->project->name}" : 'Jednokratni zadatak') . "\n" .
                "Završio: {$this->task->assignee->full_name}")
            ->options(['parse_mode' => 'HTML']);

        if ($this->task->view_url) {
            $message->button('Otvori zadatak', $this->task->view_url);
        }

        return $message;
    }

    public function toDatabase(object $notifiable): array
    {
        $taskInfo = $this->task->project
            ? "Projekt: {$this->task->project->name}"
            : 'Jednokratni zadatak';

        return FilamentNotification::make()
            ->title('Zadatak je završen!')
            ->body("Zadatak '{$this->task->title}' je završen.\n{$taskInfo}\nZavršio: {$this->task->assignee->full_name}")
            ->getDatabaseMessage();
    }
}
