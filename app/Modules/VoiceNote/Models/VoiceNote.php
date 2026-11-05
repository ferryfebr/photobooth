<?php

namespace App\Modules\VoiceNote\Models;

use App\Modules\Core\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoiceNote extends Model
{
    protected $fillable = [
        'event_id',
        'sender_name',
        'file_path',
        'original_file_path',
        'mime_type',
        'duration_seconds',
        'device_fingerprint',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'converted_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
