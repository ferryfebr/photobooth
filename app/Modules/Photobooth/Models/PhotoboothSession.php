<?php

namespace App\Modules\Photobooth\Models;

use App\Modules\Core\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhotoboothSession extends Model
{
    protected $fillable = [
        'event_id',
        'frame_id',
        'device_fingerprint',
        'status',
        'result_file_path',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function frame(): BelongsTo
    {
        return $this->belongsTo(Frame::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PhotoboothPhoto::class)->orderBy('order');
    }
}
