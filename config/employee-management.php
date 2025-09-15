<?php

// config for Amicus/FilamentEmployeeManagement
return [
    'telegram-bot-api' => [
        'general_notification' => env('TELEGRAM_CHAT_ID', '-1003036762716'),
    ],

    'resources' => [
        'task' => [
            'app' => \App\Filament\Resources\TaskResource::class,
            'package' => \Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\TaskResource::class,
        ],
        // Add more resources as needed
    ],

    'forms' => [
        'task' => [
            'app' => \App\Filament\Forms\TaskForm::class,
            'package' => \Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Schemas\TaskForm::class,
        ],
        // Add more forms as needed
    ],
];
