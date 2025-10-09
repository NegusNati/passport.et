<?php

namespace App\Listeners;

use App\Events\AdvertisementRequestCreated;
use App\Notifications\TelegramSimpleNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class NotifyTelegramAdvertisementRequest
{
    public function handle(AdvertisementRequestCreated $event): void
    {
        $chatId = config('services.telegram-bot.chat_id');

        if (! $chatId) {
            return;
        }

        $request = $event->advertisementRequest;
        $description = Str::limit($request->description, 100);
        $companyInfo = $request->company_name ? " ({$request->company_name})" : '';

        $message = sprintf(
            "🆕 New Advertisement Request\n\nFrom: %s%s\nPhone: %s\nEmail: %s\nDescription: %s\n\nCreated: %s",
            $request->full_name,
            $companyInfo,
            $request->phone_number,
            $request->email ?: 'Not provided',
            $description,
            $request->created_at->toDateTimeString()
        );

        Notification::route('telegram', $chatId)
            ->notify(new TelegramSimpleNotification($message));
    }
}
