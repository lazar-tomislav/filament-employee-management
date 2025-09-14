<?php

namespace Amicus\FilamentEmployeeManagement\Services;

use Illuminate\Notifications\Notifiable;

class GeneralNotificationTarget
{
    use Notifiable;

    public function routeNotificationForTelegram()
    {
        return config('employee-management.telegram-bot-api.general_notification');
    }

    public function getKey()
    {
        return 'general-notification';
    }
}