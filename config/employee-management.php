<?php

// config for Amicus/FilamentEmployeeManagement
return [
    'telegram-bot-api' => [
        "is_active"=>false,
        'general_notification' => env('TELEGRAM_CHAT_ID', '-1003036762716'),
    ],

    'resources' => [
    ],

    'forms' => [
    ],

    'enabled_features' => [
        'tasks' => false,
        'projects' => false,
    ],
];
