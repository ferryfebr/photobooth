<?php

use App\Modules\Core\Http\Controllers\Admin\AuthController;
use App\Modules\Core\Http\Controllers\Admin\ClientController;
use App\Modules\Core\Http\Controllers\Admin\DashboardController;
use App\Modules\Core\Http\Controllers\Admin\EventController;
use App\Modules\Core\Http\Controllers\Client\AuthController as ClientAuthController;
use App\Modules\Core\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Modules\Core\Http\Controllers\Client\EventController as ClientEventController;
use App\Modules\Core\Http\Controllers\PublicEventController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('login.store');

        Route::middleware('auth:admin')->group(function () {
            Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
            Route::get('/', DashboardController::class)->name('dashboard');

            Route::resource('clients', ClientController::class)->except('show');
            Route::get('events/{event}/qr', [EventController::class, 'qr'])->name('events.qr');
            Route::post('events/{event}/archive', [EventController::class, 'archive'])->name('events.archive');
            Route::get('events/{event}/archive', [EventController::class, 'archiveDownload'])->name('events.archive.download');
            Route::resource('events', EventController::class);
        });
    });

    Route::prefix('client')->name('client.')->group(function () {
        Route::get('login', [ClientAuthController::class, 'create'])->name('login');
        Route::post('login', [ClientAuthController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('login.store');

        Route::middleware('auth:client')->group(function () {
            Route::post('logout', [ClientAuthController::class, 'destroy'])->name('logout');
            Route::get('/', ClientDashboardController::class)->name('dashboard');
            Route::get('events/{event}', [ClientEventController::class, 'show'])->name('events.show');
            Route::post('events/{event}/archive', [ClientEventController::class, 'archive'])->name('events.archive');
            Route::get('events/{event}/archive', [ClientEventController::class, 'archiveDownload'])->name('events.archive.download');
            Route::post('guest-photos/{guestPhoto}/approve', [ClientEventController::class, 'approveGuestPhoto'])->name('guest-photos.approve');
            Route::post('guest-photos/{guestPhoto}/hide', [ClientEventController::class, 'hideGuestPhoto'])->name('guest-photos.hide');
        });
    });

    Route::get('/e/{event:slug}', PublicEventController::class)->name('events.landing');

    if ($domain = config('app.event_domain')) {
        Route::domain('{event:slug}.'.$domain)
            ->get('/', PublicEventController::class)
            ->name('events.landing.subdomain');
    }
});
