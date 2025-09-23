<?php

use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\PassportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->as('api.v1.')
    ->middleware(['api', 'throttle:api.v1.default'])
    ->group(function () {
        Route::get('/passports', [PassportController::class, 'index'])->name('passports.index');
        Route::get('/passports/{passport}', [PassportController::class, 'show'])->name('passports.show');
        Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
    });
