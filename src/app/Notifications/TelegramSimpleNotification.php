<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramSimpleNotification extends Notification
{
    public function __construct(
        protected string $message,
        protected int $deduplicateForSeconds = 5
    ) {
    }

    public function via($notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        $shouldSend = $this->shouldSendToTelegram($notifiable);

        return TelegramMessage::create()
            ->content($this->message)
            ->sendWhen($shouldSend);
    }

    protected function shouldSendToTelegram($notifiable): bool
    {
        $key = $this->notificationCacheKey($notifiable);

        return Cache::add($key, true, $this->deduplicateForSeconds);
    }

    protected function notificationCacheKey($notifiable): string
    {
        $chatId = $this->resolveChatId($notifiable);

        return 'telegram:notification:' . sha1($chatId . '|' . $this->message);
    }

    protected function resolveChatId($notifiable): string
    {
        if (method_exists($notifiable, 'routeNotificationFor')) {
            $route = $notifiable->routeNotificationFor('telegram', $this)
                ?? $notifiable->routeNotificationFor(TelegramChannel::class, $this);

            if ($route) {
                return (string) $route;
            }
        }

        return (string) (config('services.telegram-bot.chat_id') ?? 'telegram');
    }
}
