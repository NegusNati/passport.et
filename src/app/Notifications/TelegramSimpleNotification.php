<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramSimpleNotification extends Notification
{
    public function __construct(protected string $message)
    {
    }

    public function via($notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        return TelegramMessage::create()->content($this->message);
    }
}
