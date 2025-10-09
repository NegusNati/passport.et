<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Advertisement automation
Schedule::command('advertisements:notify-expiring')->dailyAt('09:00');
Schedule::command('advertisements:auto-expire')->dailyAt('00:15');
Schedule::command('advertisements:auto-activate')->everyFiveMinutes();

Schedule::command('app:generate-sitemap')->daily();
