<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\PassportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->as('api.v1.')
    ->middleware(['api', 'throttle:api.v1.default'])
    ->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

            Route::middleware('auth:sanctum')->group(function () {
                Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
                Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
            });
        });

        Route::get('/passports', [PassportController::class, 'index'])->name('passports.index');
        Route::get('/passports/{passport}', [PassportController::class, 'show'])->name('passports.show');
        Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
    });
