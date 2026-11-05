<?php

use App\Modules\Invitation\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Modules\Invitation\Http\Controllers\InvitationController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/e/{event:slug}/undangan', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/e/{event:slug}/rsvp', [InvitationController::class, 'rsvp'])
        ->middleware(['signed', 'throttle:5,1'])
        ->name('invitation.rsvp');

    Route::middleware('auth:admin')
        ->prefix('admin/events/{event}')
        ->name('admin.invitations.')
        ->group(function () {
            Route::get('undangan', [AdminInvitationController::class, 'edit'])->name('edit');
            Route::put('undangan', [AdminInvitationController::class, 'update'])->name('update');
        });
});
