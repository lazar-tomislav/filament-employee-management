<?php

namespace Amicus\FilamentEmployeeManagement\Services;

use Illuminate\Notifications\Notifiable;

class GeneralNotificationTarget
{
    use Notifiable;

    public $telegram_chat_id;

    public function __construct()
    {
        $this->telegram_chat_id = config('employee-management.telegram-bot-api.general_notification');
    }

    public function routeNotificationForTelegram()
    {
        return $this->telegram_chat_id;
    }

    public function getKey()
    {
        return 'general-notification';
    }
}