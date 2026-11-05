<?php

namespace App\Modules\Invitation\Models;

use App\Modules\Core\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationDetail extends Model
{
    protected $fillable = [
        'event_id',
        'headline',
        'description',
        'story',
        'venue_name',
        'venue_address',
        'maps_url',
        'cover_image_path',
        'gallery_images',
        'gift_info',
    ];

    protected function casts(): array
    {
        return [
            'gallery_images' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
