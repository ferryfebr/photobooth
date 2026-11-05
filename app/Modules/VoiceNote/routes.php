<?php

use App\Modules\VoiceNote\Http\Controllers\Admin\VoiceNoteController as AdminVoiceNoteController;
use App\Modules\VoiceNote\Http\Controllers\VoiceNoteController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/e/{event:slug}/ucapan', [VoiceNoteController::class, 'show'])->name('voicenote.show');
    Route::post('/e/{event:slug}/ucapan', [VoiceNoteController::class, 'store'])
        ->middleware(['signed', 'throttle:5,1'])
        ->name('voicenote.store');

    Route::delete('/admin/voice-notes/{voiceNote}', [AdminVoiceNoteController::class, 'destroy'])
        ->middleware('auth:admin')
        ->name('admin.voice-notes.destroy');
});
