<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Models\TaskUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TaskUpdateMentionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TaskUpdate $taskUpdate
    )
    {
        logger("task update mention notification created for task update ID: {$taskUpdate->id}");
    }

    public function via(object $notifiable): array
    {
        return ['telegram'];
    }
    public function toTelegram(object $notifiable): ?TelegramMessage
    {
        if(!$notifiable->telegram_chat_id){
            return null;
        }
        $body = strip_tags($this->taskUpdate->body);
        $message = TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("{$this->taskUpdate->author->full_name} vas je spomenuo u komentaru.\n\n" .
                "<strong>{$this->taskUpdate->task->title}</strong> \n\n" .
                "<blockquote>{$body}</blockquote>")
            ->options(['parse_mode' => 'HTML'])
            ->button('Otvori zadatak', $this->taskUpdate->task->view_url);

        return $message;
    }
}
