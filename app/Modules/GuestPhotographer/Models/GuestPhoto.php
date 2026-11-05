<?php

namespace App\Modules\GuestPhotographer\Models;

use App\Modules\Core\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestPhoto extends Model
{
    protected $fillable = [
        'event_id',
        'file_path',
        'device_fingerprint',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
