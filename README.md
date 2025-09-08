# Telegram

### 1. Install the package in main project
```bash
composer require laravel-notification-channels/telegram
```

### 2. Add this to config/services.php
```php
# config/services.php
'telegram-bot-api' => [
    'token' => env('TELEGRAM_BOT_TOKEN', 'YOUR BOT TOKEN HERE')
],
```

## Theme
### 1. Add to theme.css

@source '../../../../packages/filament-employee-management/resources/views/filament/**/*';
@source '../../../../packages/filament-employee-management/resources/css/index.css';
