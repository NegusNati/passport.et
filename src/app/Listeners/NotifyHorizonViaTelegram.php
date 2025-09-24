<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Notification;
use Laravel\Horizon\Events\JobFailed;
use Laravel\Horizon\Events\JobProcessing;
use Laravel\Horizon\Events\LongWaitDetected;
use App\Notifications\TelegramSimpleNotification;

class NotifyHorizonViaTelegram
{
    public function handle($event): void
    {
        $chatId = config('services.telegram-bot.chat_id');

        if (! $chatId) {
            return;
        }

        $message = $this->buildMessage($event);

        if (! $message) {
            return;
        }

        Notification::route('telegram', $chatId)
            ->notify(new TelegramSimpleNotification($message));
    }

    protected function buildMessage($event): ?string
    {
        if ($event instanceof JobFailed) {
            $job = class_basename($event->payload['displayName'] ?? 'job');

            return sprintf(
                'Horizon alert: %s failed on %s queue at %s. Exception: %s',
                $job,
                $event->queue,
                now()->toDateTimeString(),
                $event->exception->getMessage()
            );
        }

        if ($event instanceof LongWaitDetected) {
            return sprintf(
                'Horizon warning: Queue %s has been waiting %s seconds (threshold %s).',
                $event->queue,
                $event->seconds,
                $event->threshold
            );
        }

        if ($event instanceof JobProcessing) {
            $job = class_basename($event->payload['displayName'] ?? 'job');

            return sprintf(
                'Horizon notice: %s started on %s queue at %s.',
                $job,
                $event->queue,
                now()->toDateTimeString()
            );
        }

        return null;
    }
}
