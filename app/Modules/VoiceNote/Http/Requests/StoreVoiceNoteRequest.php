<?php

namespace App\Modules\VoiceNote\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreVoiceNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'audio' => [
                'required',
                'file',
                'max:20480',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! $value instanceof UploadedFile) {
                        $fail('File audio tidak valid.');

                        return;
                    }

                    $serverMime = strtolower((string) $value->getMimeType());
                    $clientMime = strtolower((string) $value->getClientMimeType());

                    $allowed = str_starts_with($serverMime, 'audio/')
                        || str_starts_with($clientMime, 'audio/')
                        || in_array($serverMime, ['video/webm', 'application/ogg', 'application/octet-stream'], true)
                        || in_array($clientMime, ['video/webm', 'application/ogg'], true);

                    if (! $allowed) {
                        $fail('Format audio tidak didukung. Gunakan rekaman dari browser.');
                    }
                },
            ],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:120'],
            'device_fingerprint' => ['nullable', 'string', 'max:100'],
        ];
    }
}
