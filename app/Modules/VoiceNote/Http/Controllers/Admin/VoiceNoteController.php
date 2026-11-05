<?php

namespace App\Modules\VoiceNote\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\VoiceNote\Models\VoiceNote;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;

class VoiceNoteController extends Controller
{
    public function destroy(VoiceNote $voiceNote, MediaStorage $media): RedirectResponse
    {
        $media->delete($voiceNote->file_path);

        if ($voiceNote->original_file_path && $voiceNote->original_file_path !== $voiceNote->file_path) {
            $media->delete($voiceNote->original_file_path);
        }

        $voiceNote->delete();

        return back()->with('status', 'Voice note dihapus.');
    }
}
