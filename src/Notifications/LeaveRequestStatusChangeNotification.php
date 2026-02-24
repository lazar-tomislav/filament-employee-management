<?php

namespace Amicus\FilamentEmployeeManagement\Notifications;

use Amicus\FilamentEmployeeManagement\Mail\LeaveRequestStatusNotification;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Middleware\RateLimited;
use NotificationChannels\Telegram\TelegramMessage;

class LeaveRequestStatusChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 10;

    public int $maxExceptions = 3;

    /**
     * @return array<int, object>
     */
    public function middleware(object $notifiable, string $channel): array
    {
        return match ($channel) {
            'mail' => [(new RateLimited('resend-api'))->releaseAfter(3)],
            default => [],
        };
    }

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];

        if (config('employee-management.telegram-bot-api.is_active') && $notifiable->employee?->telegram_chat_id) {
            $channels[] = 'telegram';
        }

        return $channels;
    }

    public function toMail(object $notifiable): LeaveRequestStatusNotification
    {
        return new LeaveRequestStatusNotification($this->leaveRequest);
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

        $status = $this->leaveRequest->status->getLabel();
        $startDate = $this->leaveRequest->start_date->format('d.m.Y');
        $endDate = $this->leaveRequest->end_date->format('d.m.Y');

        return TelegramMessage::create()
            ->to($telegramChatId)
            ->content("Zahtjev za godišnji ({$startDate} - {$endDate}) ima novi status: {$status}\n\nRazlog: " . ($this->leaveRequest->rejection_reason ?? 'Nije naveden'));
    }

    public function toDatabase(object $notifiable): array
    {
        $status = $this->leaveRequest->status->getLabel();
        $startDate = $this->leaveRequest->start_date->format('d.m.Y');
        $endDate = $this->leaveRequest->end_date->format('d.m.Y');

        return FilamentNotification::make()
            ->title('Zahtjev za godišnji ažuriran')
            ->body("Zahtjev za godišnji ({$startDate} - {$endDate}) ima novi status: {$status}\nRazlog: " . ($this->leaveRequest->rejection_reason ?? 'Nije naveden'))
            ->getDatabaseMessage();
    }
}
