<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\PassportController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\TagController as PublicTagController;
use App\Http\Controllers\Api\V1\Admin\ArticleAdminController;
use App\Http\Controllers\Api\V1\Admin\PDFToSQLiteController as AdminPDFToSQLiteController;
use App\Http\Controllers\Api\V1\Admin\UserAdminController;
use App\Http\Controllers\Api\V1\Admin\AdminAbilityController;
use App\Http\Controllers\Api\V1\Admin\AdvertisementRequestAdminController;
use App\Http\Controllers\Api\V1\Admin\AdvertisementAdminController;
use App\Http\Controllers\Api\V1\AdvertisementRequestController;
use App\Http\Controllers\Api\V1\AdvertisementController;
use App\Http\Controllers\Api\V1\FeedController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->as('api.v1.')
    ->middleware(['api', 'throttle:api.v1.default'])
    ->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
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

        // Advertisement Requests - public
        Route::post('/advertisement-requests', [AdvertisementRequestController::class, 'store'])->name('advertisement-requests.store');

        // Advertisements - public (for frontend display and tracking)
        Route::get('/advertisements/active', [AdvertisementController::class, 'active'])->name('advertisements.active');
        Route::post('/advertisements/{advertisement}/impression', [AdvertisementController::class, 'impression'])->name('advertisements.impression');
        Route::post('/advertisements/{advertisement}/click', [AdvertisementController::class, 'click'])->name('advertisements.click');

        // Articles - admin (manage content)
        Route::prefix('admin')->middleware(['auth:sanctum', 'can:manage-articles'])->group(function () {
            Route::post('/articles', [ArticleAdminController::class, 'store'])->name('admin.articles.store');
            // Support both PUT (for JSON) and POST with _method=PUT (for multipart/form-data with files)
            Route::match(['put', 'post'], '/articles/{article:slug}', [ArticleAdminController::class, 'update'])->name('admin.articles.update');
            Route::delete('/articles/{article:slug}', [ArticleAdminController::class, 'destroy'])->name('admin.articles.destroy');
        });

        Route::prefix('admin')->middleware(['auth:sanctum', 'can:upload-files'])->group(function () {
            Route::get('/pdf-to-sqlite', [AdminPDFToSQLiteController::class, 'create'])->name('admin.pdf-to-sqlite.create');
            Route::post('/pdf-to-sqlite', [AdminPDFToSQLiteController::class, 'store'])->name('admin.pdf-to-sqlite.store');
        });

        Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
            Route::get('/users', [UserAdminController::class, 'index'])->name('admin.users.index');
            Route::get('/users/{user}', [UserAdminController::class, 'show'])->name('admin.users.show');
            Route::patch('/users/{user}/role', [UserAdminController::class, 'updateRole'])->name('admin.users.update-role');
            Route::get('/abilities', [AdminAbilityController::class, 'show'])->name('admin.abilities.show');
        });

        Route::prefix('admin')->middleware(['auth:sanctum', 'can:manage-advertisements'])->group(function () {
            Route::get('/advertisement-requests', [AdvertisementRequestAdminController::class, 'index'])->name('admin.advertisement-requests.index');
            Route::get('/advertisement-requests/{advertisementRequest}', [AdvertisementRequestAdminController::class, 'show'])->name('admin.advertisement-requests.show');
            Route::patch('/advertisement-requests/{advertisementRequest}', [AdvertisementRequestAdminController::class, 'update'])->name('admin.advertisement-requests.update');
            Route::delete('/advertisement-requests/{advertisementRequest}', [AdvertisementRequestAdminController::class, 'destroy'])->name('admin.advertisement-requests.destroy');

            // Advertisement CRM - admin
            Route::get('/advertisements', [AdvertisementAdminController::class, 'index'])->name('admin.advertisements.index');
            Route::get('/advertisements/stats', [AdvertisementAdminController::class, 'stats'])->name('admin.advertisements.stats');
            Route::post('/advertisements', [AdvertisementAdminController::class, 'store'])->name('admin.advertisements.store');
            Route::get('/advertisements/{advertisement}', [AdvertisementAdminController::class, 'show'])->name('admin.advertisements.show');
            Route::patch('/advertisements/{advertisement}', [AdvertisementAdminController::class, 'update'])->name('admin.advertisements.update');
            Route::delete('/advertisements/{advertisement}', [AdvertisementAdminController::class, 'destroy'])->name('admin.advertisements.destroy');
            Route::post('/advertisements/{id}/restore', [AdvertisementAdminController::class, 'restore'])->name('admin.advertisements.restore');
        });

        // Feeds (XML)
        Route::get('/feeds/articles.rss', [FeedController::class, 'articlesRss'])->name('feeds.articles.rss');
        Route::get('/feeds/articles.atom', [FeedController::class, 'articlesAtom'])->name('feeds.articles.atom');
    });
