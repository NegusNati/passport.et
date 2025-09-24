<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;
use Laravel\Horizon\Events\JobFailed;
use Laravel\Horizon\Events\JobProcessing;
use Laravel\Horizon\Events\JobProcessed;
use Laravel\Horizon\Events\LongWaitDetected;
use Laravel\Horizon\Events\WorkerStopping;
use App\Notifications\TelegramSimpleNotification;

class NotifyHorizonViaTelegram
{
    public function handle($event): void
    {
        if (! config('horizon-notifications.enabled', true)) {
            return;
        }

        $chatId = config('services.telegram-bot.chat_id');

        if (! $chatId) {
            return;
        }

        if (! $this->shouldNotify($event)) {
            return;
        }

        if (! $this->checkRateLimit($event)) {
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
                '🚨 Horizon Alert: %s failed on %s queue at %s. Exception: %s',
                $job,
                $event->queue,
                now()->toDateTimeString(),
                $event->exception->getMessage()
            );
        }

        if ($event instanceof LongWaitDetected) {
            return sprintf(
                '⚠️ Horizon Warning: Queue %s has been waiting %s seconds (threshold %s).',
                $event->queue,
                $event->seconds,
                $event->threshold
            );
        }

        if ($event instanceof JobProcessing) {
            $job = class_basename($event->payload['displayName'] ?? 'job');

            return sprintf(
                '🚀 Horizon Notice: %s started processing on %s queue at %s.',
                $job,
                $event->queue,
                now()->toDateTimeString()
            );
        }

        if ($event instanceof JobProcessed) {
            $job = class_basename($event->payload['displayName'] ?? 'job');

            return sprintf(
                '✅ Horizon Success: %s completed on %s queue at %s.',
                $job,
                $event->queue,
                now()->toDateTimeString()
            );
        }

        if ($event instanceof WorkerStopping) {
            return sprintf(
                '🛑 Horizon Notice: Worker stopping on %s queue at %s (status: %s).',
                $event->queue ?? 'unknown',
                now()->toDateTimeString(),
                $event->status ?? 'unknown'
            );
        }

        return null;
    }

    protected function shouldNotify($event): bool
    {
        $eventKey = $this->getEventKey($event);

        if (! $eventKey || ! config("horizon-notifications.events.{$eventKey}", false)) {
            return false;
        }

        // Check if we should filter by job class
        $allowedJobClasses = config('horizon-notifications.job_classes', []);
        if (! empty($allowedJobClasses) && isset($event->payload['displayName'])) {
            $jobClass = $event->payload['displayName'];
            if (! in_array($jobClass, $allowedJobClasses)) {
                return false;
            }
        }

        // Check if we should filter by queue
        $allowedQueues = config('horizon-notifications.queues', []);
        if (! empty($allowedQueues) && isset($event->queue)) {
            if (! in_array($event->queue, $allowedQueues)) {
                return false;
            }
        }

        return true;
    }

    protected function checkRateLimit($event): bool
    {
        $eventKey = $this->getEventKey($event);

        if (! $eventKey) {
            return true;
        }

        $rateLimit = config("horizon-notifications.rate_limits.{$eventKey}", 0);

        if ($rateLimit <= 0) {
            return true;
        }

        $cacheKey = "horizon_notification_rate_limit_{$eventKey}";
        $currentCount = Cache::get($cacheKey, 0);

        if ($currentCount >= $rateLimit) {
            return false;
        }

        Cache::put($cacheKey, $currentCount + 1, 60); // 1 minute TTL

        return true;
    }

    protected function getEventKey($event): ?string
    {
        return match (true) {
            $event instanceof JobFailed => 'job_failed',
            $event instanceof JobProcessing => 'job_processing',
            $event instanceof JobProcessed => 'job_processed',
            $event instanceof LongWaitDetected => 'long_wait_detected',
            $event instanceof WorkerStopping => 'worker_stopping',
            default => null,
        };
    }
}
