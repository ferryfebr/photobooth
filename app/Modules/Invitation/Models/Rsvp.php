<?php

namespace App\Modules\Invitation\Models;

use App\Modules\Core\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rsvp extends Model
{
    protected $fillable = [
        'event_id',
        'guest_name',
        'attendance',
        'guest_count',
        'note',
        'device_fingerprint',
    ];

    protected function casts(): array
    {
        return [
            'guest_count' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
