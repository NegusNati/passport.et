<?php

namespace App\Providers;

use App\Domain\Passport\Models\Passport;
use App\Models\User;
use App\Domain\Article\Models\Article;
use App\Domain\Advertisement\Models\AdvertisementRequest;
use App\Domain\Advertisement\Models\Advertisement;
use App\Observers\ArticleObserver;
use App\Observers\PassportObserver;
use App\Observers\AdvertisementRequestObserver;
use App\Observers\AdvertisementObserver;
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
        // Force HTTPS in production, respecting proxy headers
        if (config('app.env') === 'production') {
            // Check if the original request was HTTPS (from proxy headers)
            if (request()->header('X-Forwarded-Proto') === 'https') {
                URL::forceScheme('https');
            }
        }

        Passport::observe(PassportObserver::class);
        Article::observe(ArticleObserver::class);
        AdvertisementRequest::observe(AdvertisementRequestObserver::class);
        Advertisement::observe(AdvertisementObserver::class);

        $this->defineGates();

        RateLimiter::for('rateLimiter', function ($request) {
            $user = Auth::user();

            if ($user && $user->subscription) {
                $plan = $user->subscription->plan;

                return $plan === 'premium'
                    ? Limit::perHour(100000)->by($user->id)->response(function () {
                        return Inertia::render('Errors/RateLimitExceeded', [
                            'message' => 'You have exceeded the hourly rate limit for premium users.',
                        ])->toResponse(request())->setStatusCode(429);
                    })
                    : Limit::perHour(10000)->by($user->id)->response(function () {
                        return Inertia::render('Errors/RateLimitExceeded', [
                            'message' => 'You have exceeded the hourly rate limit for standard users. we will add premium plan soon.',
                        ])->toResponse(request())->setStatusCode(429);
                    });
            }

            if (optional($user)->id) {
                return Limit::perHour(10000)->by($user->id)->response(function () {
                    return Inertia::render('Errors/RateLimitExceeded', [
                        'message' => 'You have exceeded the hourly rate limit for authenticated users without a subscription.',
                    ])->toResponse(request())->setStatusCode(429);
                });
            }

            return Limit::perHour(10000)->by(optional($user)->id ?: $request->ip())->response(function () {
                return Inertia::render('Errors/RateLimitExceeded', [
                    'message' => 'You have exceeded the hourly rate limit for unauthenticated users. You should Log In to use it more. Or',
                ])->toResponse(request())->setStatusCode(429);
            });
        });

        RateLimiter::for('api.v1.default', function (Request $request) {
            $user = $request->user();

            if ($user && $user->subscription && $user->subscription->plan === 'premium') {
                return Limit::perMinute(240)
                    ->by('user:'.$user->id)
                    ->response(fn () => $this->apiThrottleResponse());
            }

            if ($user) {
                return Limit::perMinute(120)
                    ->by('user:'.$user->id)
                    ->response(fn () => $this->apiThrottleResponse());
            }

            return Limit::perMinute(60)
                ->by('ip:'.$request->ip())
                ->response(fn () => $this->apiThrottleResponse());
        });
    }

    /**
     * Standard JSON payload for throttled API responses.
     */
    protected function apiThrottleResponse()
    {
        return response()->json([
            'status' => 'error',
            'code' => 'rate_limit_exceeded',
            'message' => 'Too many requests. Please slow down and try again shortly.',
        ], 429);
    }

    protected function defineGates(): void
    {
        Gate::define('viewPulse', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('viewHorizon', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('manage-articles', function (User $user) {
            return method_exists($user, 'hasRole') ? $user->hasRole('admin') : true;
        });

        Gate::define('manage-advertisements', function (User $user) {
            return method_exists($user, 'hasRole') ? $user->hasRole('admin') : true;
        });
    }
}
