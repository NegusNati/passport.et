<?php

use App\Http\Controllers\PassportSearchController;
use App\Http\Controllers\PDFToSQLiteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\LanguageSwitcherController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\TelegramPDFController_depreciated;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;




Route::post('/pdf-to-sqlite',  [PDFToSQLiteController::class, 'store'])->name('pdf-to-sqlite')->can('upload-files');
Route::get('/pdf-to-sqlite',  [PDFToSQLiteController::class, 'create'])->can('upload-files');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');



//adde on sep 24/24
Route::middleware(['throttle:rateLimiter'])->group(function () {
    Route::get('/passport', [PassportSearchController::class, 'index'])->name('passport');
    Route::get('/passport/search', [PassportSearchController::class, 'show'])->name('passport.show');
    Route::post('/passport', [PassportSearchController::class, 'show'])->name('passport.show');
    Route::get('/passport/{id}', [PassportSearchController::class, 'detail'])->name('passport.showDetail');
    Route::get('/all-passports', [PassportSearchController::class, 'all'])->name('passport.all');
});

// Keep the dashboard route as it is, since it should remain protected
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');







// on auth sep 24/24
// Route::middleware(['auth', 'throttle:rateLimiter'])->group(function () {

//     Route::get('/passport', [PassportSearchController::class, 'index'])->name('passport');
//     Route::get('/passport/search', [PassportSearchController::class, 'show'])->name('passport.show');
//     Route::post('/passport', [PassportSearchController::class, 'show'])->name('passport.show');
//     Route::get('/passport/{id}', [PassportSearchController::class, 'detail'])->name('passport.showDetail');
//     Route::get('/all-passports', [PassportSearchController::class, 'all'])->name('passport.all');


//     // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });


// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/privacy', function () {
    return Inertia::render('Terms/PrivacyPolicy');
})->name('privacy');

Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->middleware('auth')->name('subscribe');
Route::get(
    '/subscribe',
    function () {
        return Inertia::render('Subscription/Subscribe');
        // return view('subscribe');
    }
)->middleware('auth');

Route::get('/telegram', function () {
    return Inertia::render('Telegram/Index');
})->middleware('auth')->name('telegram.index');




Route::options('{any}', function () {
    return response()->json([], 200);
})->where('any', '.*');

Route::post('/change-locale', [LocaleController::class, 'changeLocale'])->name('change-locale');

// The route that the button calls to initialize payment
Route::get('/payment', function () {
 return Inertia::render('Payment', [
        'amount' => request()->amount,
    ]);
})->middleware('auth')->name('payment');
// The route that the button calls to initialize payment

Route::post('/pay', 'App\Http\Controllers\ChapaController@initialize')->middleware('auth')->name('pay');

// The callback url after a payment
Route::get('callback/{reference}', 'App\Http\Controllers\ChapaController@callback')->middleware('auth')->name('callback');



//webhook for pdf Depreciated feature
// Route::get('/set-webhook', [TelegramPDFController_depreciated::class, 'setWebhook']);
// Route::post('/telegram/webhook', [TelegramPDFController_depreciated::class, 'handleWebhook']);
// Route::post('/telegram/webhook', [TelegramPDFController::class, 'handleWebhook'])->middleware('throttle:rateLimiter');


Route::fallback(function () {
    return Inertia::render('PageNotFound');
});

require __DIR__ . '/auth.php';
