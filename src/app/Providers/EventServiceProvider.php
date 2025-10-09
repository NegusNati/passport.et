<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Registered;
use Laravel\Horizon\Events\JobFailed;
use Laravel\Horizon\Events\JobProcessing;
use Laravel\Horizon\Events\JobProcessed;
use Laravel\Horizon\Events\LongWaitDetected;
use Laravel\Horizon\Events\WorkerStopping;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        JobFailed::class => [
            \App\Listeners\NotifyHorizonViaTelegram::class,
        ],
        LongWaitDetected::class => [
            \App\Listeners\NotifyHorizonViaTelegram::class,
        ],
        JobProcessing::class => [
            \App\Listeners\NotifyHorizonViaTelegram::class,
        ],
        JobProcessed::class => [
            \App\Listeners\NotifyHorizonViaTelegram::class,
        ],
        WorkerStopping::class => [
            \App\Listeners\NotifyHorizonViaTelegram::class,
        ],
        Registered::class => [
            \App\Listeners\NotifyTelegramUserRegistered::class,
        ],
        \App\Events\AdvertisementRequestCreated::class => [
            \App\Listeners\NotifyTelegramAdvertisementRequest::class,
        ],
        \App\Events\AdvertisementExpiring::class => [
            \App\Listeners\NotifyTelegramAdvertisementExpiring::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
