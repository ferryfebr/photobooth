<?php

namespace App\Modules\VoiceNote\Jobs;

use App\Modules\VoiceNote\Models\VoiceNote;
use App\Support\MediaStorage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Throwable;

class ConvertAudioJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public int $voiceNoteId) {}

    public function handle(MediaStorage $media): void
    {
        $voiceNote = VoiceNote::find($this->voiceNoteId);

        if (! $voiceNote) {
            return;
        }

        $disk = Storage::disk($media->disk());
        $source = $voiceNote->original_file_path ?: $voiceNote->file_path;

        if (! $disk->exists($source)) {
            return;
        }

        $input = tempnam(sys_get_temp_dir(), 'voice-in-');
        $output = tempnam(sys_get_temp_dir(), 'voice-out-').'.mp3';

        if ($input === false) {
            return;
        }

        file_put_contents($input, $disk->get($source));

        $ffmpeg = config('media.ffmpeg_path', 'ffmpeg');
        $process = new Process([
            $ffmpeg,
            '-y',
            '-i', $input,
            '-vn',
            '-acodec', 'libmp3lame',
            '-q:a', '4',
            $output,
        ]);

        try {
            $process->setTimeout(90);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($output) || filesize($output) === 0) {
                Log::warning('ConvertAudioJob: FFmpeg tidak tersedia atau gagal, file asli dipertahankan.', [
                    'voice_note_id' => $voiceNote->id,
                    'error' => trim($process->getErrorOutput()),
                ]);

                return;
            }

            $newPath = 'voice-notes/'.Str::uuid().'.mp3';
            $disk->put($newPath, file_get_contents($output));

            $voiceNote->update([
                'file_path' => $newPath,
                'mime_type' => 'audio/mpeg',
                'converted_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('ConvertAudioJob: konversi gagal, file asli dipertahankan.', [
                'voice_note_id' => $voiceNote->id,
                'message' => $exception->getMessage(),
            ]);
        } finally {
            @unlink($input);
            @unlink($output);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('ConvertAudioJob gagal', [
            'voice_note_id' => $this->voiceNoteId,
            'message' => $exception->getMessage(),
        ]);
    }
}
