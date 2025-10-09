<?php

namespace App\Listeners;

use App\Events\AdvertisementExpiring;
use App\Notifications\TelegramSimpleNotification;
use Illuminate\Support\Facades\Notification;

class NotifyTelegramAdvertisementExpiring
{
    public function handle(AdvertisementExpiring $event): void
    {
        $chatId = config('services.telegram-bot.chat_id');

        if (! $chatId) {
            return;
        }

        $ad = $event->advertisement;
        $daysLeft = $ad->daysUntilExpiry();

        $message = sprintf(
            "⚠️ Advertisement Expiring Soon\n\nSlot: %s\nTitle: %s\nClient: %s\nEnding Date: %s (%s)\nPayment: $%s (%s)\n\nStatus: %s\nImpressions: %s\nClicks: %s",
            $ad->ad_slot_number,
            $ad->ad_title,
            $ad->client_name ?: 'Not specified',
            $ad->ad_ending_date->format('Y-m-d'),
            $daysLeft === 1 ? '1 day left' : "{$daysLeft} days left",
            number_format($ad->payment_amount, 2),
            ucfirst($ad->payment_status),
            ucfirst($ad->status),
            number_format($ad->impressions_count),
            number_format($ad->clicks_count)
        );

        Notification::route('telegram', $chatId)
            ->notify(new TelegramSimpleNotification($message));
    }
}
