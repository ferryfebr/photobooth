<?php

namespace App\Modules\Invitation\Models;

use App\Modules\Core\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationTheme extends Model
{
    protected $fillable = [
        'slug',
        'label',
        'created_for_event_id',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'created_for_event_id');
    }
}
