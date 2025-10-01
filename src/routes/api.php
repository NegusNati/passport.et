<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\PassportController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\TagController as PublicTagController;
use App\Http\Controllers\Api\V1\Admin\ArticleAdminController;
use App\Http\Controllers\Api\V1\FeedController;
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

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        });

        Route::get('/passports', [PassportController::class, 'index'])->name('passports.index');
        Route::get('/passports/{passport}', [PassportController::class, 'show'])->name('passports.show');
        Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');

        // Articles - public
        Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
        Route::get('/articles/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');
        Route::get('/categories', [PublicCategoryController::class, 'index'])->name('categories.index');
        Route::get('/tags', [PublicTagController::class, 'index'])->name('tags.index');

        // Articles - admin (manage content)
        Route::prefix('admin')->middleware(['auth:sanctum', 'can:manage-articles'])->group(function () {
            Route::post('/articles', [ArticleAdminController::class, 'store'])->name('admin.articles.store');
            Route::patch('/articles/{article:slug}', [ArticleAdminController::class, 'update'])->name('admin.articles.update');
            Route::delete('/articles/{article:slug}', [ArticleAdminController::class, 'destroy'])->name('admin.articles.destroy');
        });

        // Feeds (XML)
        Route::get('/feeds/articles.rss', [FeedController::class, 'articlesRss'])->name('feeds.articles.rss');
        Route::get('/feeds/articles.atom', [FeedController::class, 'articlesAtom'])->name('feeds.articles.atom');
    });


    
