<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        Gate::define('viewPulse', function (User $user) {
            return $user->hasRole('admin');
        });

        RateLimiter::for('rateLimiter', function ($request) {
            $user = Auth::user();

            if ($user && $user->subscription) {
                $plan = $user->subscription->plan;

                return $plan === 'premium'
                    ? Limit::perHour(1000)->by($user->id)->response(function () {
                        return Inertia::render('Errors/RateLimitExceeded', [
                            'message' => 'You have exceeded the hourly rate limit for premium users.',
                        ])->toResponse(request())->setStatusCode(429);
                    })
                : Limit::perHour(100)->by($user->id)->response(function () {
                        return Inertia::render('Errors/RateLimitExceeded', [
                            'message' => 'You have exceeded the hourly rate limit for standard users.',
                        ])->toResponse(request())->setStatusCode(429);
                    });
            }

            if (optional($user)->id) {
                return Limit::perHour(60)->by($user->id)->response(function () {
                    return Inertia::render('Errors/RateLimitExceeded', [
                        'message' => 'You have exceeded the hourly rate limit for authenticated users without a subscription.',
                    ])->toResponse(request())->setStatusCode(429);
                });
            }

            return Limit::perHour(60)->by(optional($user)->id ?: $request->ip())->response(function () {
                return Inertia::render('Errors/RateLimitExceeded', [
                    'message' => 'You have exceeded the hourly rate limit for unauthenticated users.',
                ])->toResponse(request())->setStatusCode(429);
            });
        });
    }
}
