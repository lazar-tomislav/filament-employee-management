<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use App\Models\Activity;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class ActivityMentionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Activity $activity
    ) {
        logger("activity mention notification created for activity ID: {$activity->id}");
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (config('employee-management.telegram-bot-api.is_active') && $notifiable->employee?->telegram_chat_id) {
            $channels[] = 'telegram';
        }

        return $channels;
    }

    public function toTelegram(object $notifiable): ?TelegramMessage
    {
        if (! config('employee-management.telegram-bot-api.is_active')) {
            return null;
        }

        $telegramChatId = $notifiable->employee?->telegram_chat_id;

        if (! $telegramChatId) {
            return null;
        }

        $body = strip_tags($this->activity->body);
        $entityName = $this->getEntityName();

        return TelegramMessage::create()
            ->to($telegramChatId)
            ->content("{$this->activity->author->first_name} {$this->activity->author->last_name} vas je spomenuo u komentaru.\n\n" .
                "<strong>{$entityName}</strong> \n\n" .
                "<blockquote>{$body}</blockquote>")
            ->options(['parse_mode' => 'HTML'])
            ->button($this->getButtonText(), $this->activity->activityable->view_url);
    }

    public function toDatabase(object $notifiable): array
    {
        $body = strip_tags($this->activity->body);
        $entityName = $this->getEntityName();

        return FilamentNotification::make()
            ->title('Spomenuti ste u komentaru')
            ->body("{$this->activity->author->first_name} {$this->activity->author->last_name} vas je spomenuo u komentaru za {$this->getEntityTypeLabel()}: {$entityName}")
            ->getDatabaseMessage();
    }

    private function getEntityName(): string
    {
        return match (class_basename($this->activity->activityable_type)) {
            'Project' => $this->activity->activityable->name ?? 'Projekt',
            'Task' => $this->activity->activityable->title ?? 'Zadatak',
            default => 'Entitet'
        };
    }

    private function getEntityTypeLabel(): string
    {
        return match (class_basename($this->activity->activityable_type)) {
            'Project' => 'projekt',
            'Task' => 'zadatak',
            default => 'entitet'
        };
    }

    private function getButtonText(): string
    {
        return match (class_basename($this->activity->activityable_type)) {
            'Project' => 'Otvori projekt',
            'Task' => 'Otvori zadatak',
            default => 'Otvori'
        };
    }
}
