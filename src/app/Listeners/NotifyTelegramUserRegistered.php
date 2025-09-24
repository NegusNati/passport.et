<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TelegramSimpleNotification;

class NotifyTelegramUserRegistered
{
    public function handle(Registered $event): void
    {
        $chatId = config('services.telegram-bot.chat_id');

        if (! $chatId) {
            return;
        }

        $user = $event->user;
        $message = sprintf(
            'New user registered: %s (%s) at %s.',
            $user->name ?? trim($user->first_name.' '.$user->last_name),
            $user->email,
            now()->toDateTimeString()
        );

        Notification::route('telegram', $chatId)
            ->notify(new TelegramSimpleNotification($message));
    }
}
