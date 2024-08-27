<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class LocaleMiddleware
{
    public function handle($request, Closure $next)
    {
        // Example logic to set locale based on request
        // $locale = ...;
        // App::setLocale($locale);

        return $next($request);
    }
}
