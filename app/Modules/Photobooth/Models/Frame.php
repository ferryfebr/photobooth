<?php

namespace App\Modules\Photobooth\Models;

use App\Modules\Core\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Frame extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'file_path',
        'slot_coordinates',
        'photo_count',
    ];

    protected function casts(): array
    {
        return [
            'slot_coordinates' => 'array',
            'photo_count' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
