<?php

namespace App\Modules\VoiceNote\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\ActivityLog;
use App\Modules\Core\Models\Event;
use App\Modules\Core\Models\EventModule;
use App\Modules\VoiceNote\Events\VoiceNoteReceived;
use App\Modules\VoiceNote\Http\Requests\StoreVoiceNoteRequest;
use App\Modules\VoiceNote\Jobs\ConvertAudioJob;
use App\Modules\VoiceNote\Models\VoiceNote;
use App\Support\MediaStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class VoiceNoteController extends Controller
{
    public function show(Event $event, MediaStorage $media): View
    {
        $isPreview = auth('admin')->check();

        abort_unless($isPreview || in_array($event->status, ['active', 'completed'], true), 404);
        abort_unless($isPreview || $event->isModuleActive('voice_note'), 404);

        return view('voice-note.show', [
            'event' => $event,
            'storeUrl' => URL::temporarySignedRoute('voicenote.store', now()->addHours(6), ['event' => $event->slug]),
            'voiceNotes' => $event->voiceNotes()
                ->latest()
                ->limit(30)
                ->get()
                ->map(fn (VoiceNote $voiceNote) => [
                    'id' => $voiceNote->id,
                    'sender_name' => $voiceNote->sender_name,
                    'url' => $media->url($voiceNote->file_path),
                    'duration_seconds' => $voiceNote->duration_seconds,
                ]),
        ]);
    }

    public function store(StoreVoiceNoteRequest $request, Event $event, MediaStorage $media): JsonResponse
    {
        abort_unless($event->isModuleActive('voice_note'), 404);

        $fingerprint = $request->input('device_fingerprint');

        if ($fingerprint && VoiceNote::where('event_id', $event->id)->where('device_fingerprint', $fingerprint)->count() >= 3) {
            return response()->json(['message' => 'Batas 3 voice note per tamu sudah tercapai.'], 422);
        }

        $file = $request->file('audio');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'webm');

        if (! in_array($extension, ['webm', 'mp3', 'wav', 'm4a', 'ogg'], true)) {
            $extension = 'webm';
        }

        $path = $media->storeAs($file, 'voice-notes', $extension);

        $voiceNote = VoiceNote::create([
            'event_id' => $event->id,
            'sender_name' => $request->input('sender_name'),
            'file_path' => $path,
            'original_file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'duration_seconds' => (int) $request->input('duration_seconds', 0),
            'device_fingerprint' => $fingerprint,
        ]);

        ConvertAudioJob::dispatch($voiceNote->id);
        VoiceNoteReceived::dispatch($voiceNote);
        ActivityLog::record($event->id, EventModule::VOICE_NOTE, 'voice_note_recorded');

        return response()->json([
            'message' => 'Voice note berhasil dikirim.',
            'id' => $voiceNote->id,
        ]);
    }
}
