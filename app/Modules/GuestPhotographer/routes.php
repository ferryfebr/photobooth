<?php

use App\Modules\GuestPhotographer\Http\Controllers\Admin\GuestPhotoController;
use App\Modules\GuestPhotographer\Http\Controllers\GuestPhotographerController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/e/{event:slug}/fotografer', [GuestPhotographerController::class, 'show'])->name('guest.show');
    Route::post('/e/{event:slug}/fotografer', [GuestPhotographerController::class, 'store'])
        ->middleware(['signed', 'throttle:20,1'])
        ->name('guest.store');

    Route::middleware('auth:admin')->group(function () {
        Route::post('/admin/guest-photos/{guestPhoto}/approve', [GuestPhotoController::class, 'approve'])->name('admin.guest-photos.approve');
        Route::post('/admin/guest-photos/{guestPhoto}/hide', [GuestPhotoController::class, 'hide'])->name('admin.guest-photos.hide');
        Route::delete('/admin/guest-photos/{guestPhoto}', [GuestPhotoController::class, 'destroy'])->name('admin.guest-photos.destroy');
    });
});
