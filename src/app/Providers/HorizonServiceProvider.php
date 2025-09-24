<?php

namespace App\Providers;

use Horizon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class HorizonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Horizon::auth(function ($request) {
            $user = $request->user();

            return $user && $user->hasRole('admin');
        });
    }
}
