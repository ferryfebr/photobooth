<?php

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'event_id',
        'module_key',
        'action',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public static function record(int $eventId, string $moduleKey, string $action): self
    {
        return static::create([
            'event_id' => $eventId,
            'module_key' => $moduleKey,
            'action' => $action,
        ]);
    }
}
