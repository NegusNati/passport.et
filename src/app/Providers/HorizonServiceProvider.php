<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class HorizonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! class_exists('Laravel\\Horizon\\Horizon')) {
            return;
        }

        Horizon::auth(function ($request) {
            $user = $request->user();

            return $user && $user->hasRole('admin');
        });
    }
}
