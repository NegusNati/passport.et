<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Horizon\Events\JobFailed;
use Laravel\Horizon\Events\LongWaitDetected;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        JobFailed::class => [
            \App\Listeners\NotifyHorizonViaTelegram::class,
        ],
        LongWaitDetected::class => [
            \App\Listeners\NotifyHorizonViaTelegram::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
