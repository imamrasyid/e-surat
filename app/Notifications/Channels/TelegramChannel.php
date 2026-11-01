<?php

namespace App\Notifications\Channels;

use App\Models\User;
use App\Services\TelegramBotClient;
use Illuminate\Notifications\Notification;
use RuntimeException;

class TelegramChannel
{
    public function __construct(
        private readonly TelegramBotClient $telegram
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notifiable instanceof User) {
            return;
        }

        $chatId = $notifiable->routeNotificationForTelegram();

        if (blank($chatId)) {
            return;
        }

        if (! method_exists($notification, 'toTelegram')) {
            throw new RuntimeException(sprintf(
                'Notification [%s] tidak memiliki method toTelegram().',
                $notification::class
            ));
        }

        $this->telegram->sendMessage($chatId, $notification->toTelegram($notifiable));
    }
}
