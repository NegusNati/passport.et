<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $planType = $user->plan_type;
        $rateLimit = $this->getRateLimit($planType);

        $cacheKey = "rate_limit_{$user->id}_{$planType}";
        $requestsCount = Cache::get($cacheKey, 0);

        if ($requestsCount >= $rateLimit) {
            return response()->json(['error' => 'Rate limit exceeded'], 429);
        }

        $requestsCount++;
        Cache::put($cacheKey, $requestsCount, now()->addHour());

        return $next($request);
    }

    private function getRateLimit($planType)
    {
        switch ($planType) {
            case 'guest':
                return 10;
            case 'premium':
                return 1000;
            default:
                return 0; // or throw an exception
        }
    }
}
