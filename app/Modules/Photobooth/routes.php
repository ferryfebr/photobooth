<?php

use App\Modules\Photobooth\Http\Controllers\Admin\FrameController;
use App\Modules\Photobooth\Http\Controllers\PhotoboothController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/e/{event:slug}/photobooth', [PhotoboothController::class, 'show'])->name('photobooth.show');
    Route::post('/e/{event:slug}/photobooth', [PhotoboothController::class, 'store'])
        ->middleware(['signed', 'throttle:10,1'])
        ->name('photobooth.store');
    Route::get('/e/{event:slug}/photobooth/hasil/{session}', [PhotoboothController::class, 'result'])
        ->middleware('signed')
        ->name('photobooth.result');

    Route::middleware('auth:admin')
        ->prefix('admin/events/{event}/frames')
        ->name('admin.frames.')
        ->group(function () {
            Route::get('create', [FrameController::class, 'create'])->name('create');
            Route::post('/', [FrameController::class, 'store'])->name('store');
            Route::get('{frame}/edit', [FrameController::class, 'edit'])->name('edit');
            Route::put('{frame}', [FrameController::class, 'update'])->name('update');
            Route::delete('{frame}', [FrameController::class, 'destroy'])->name('destroy');
        });
});
