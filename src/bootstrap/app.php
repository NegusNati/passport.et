<?php

use App\Console\Commands\RedisPingCommand;
use App\Console\Commands\NotifyExpiringAdvertisements;
use App\Console\Commands\AutoExpireAdvertisements;
use App\Console\Commands\AutoActivateAdvertisements;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        RedisPingCommand::class,
        NotifyExpiringAdvertisements::class,
        AutoExpireAdvertisements::class,
        AutoActivateAdvertisements::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Trust all proxies (Cloudflare + nginx-proxy-manager)
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
