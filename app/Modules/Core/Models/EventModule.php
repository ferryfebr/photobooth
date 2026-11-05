<?php

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventModule extends Model
{
    public const PHOTOBOOTH = 'photobooth';

    public const GUEST_PHOTOGRAPHER = 'guest_photographer';

    public const VOICE_NOTE = 'voice_note';

    public const INVITATION = 'invitation';

    public const MODULE_KEYS = [
        self::PHOTOBOOTH,
        self::GUEST_PHOTOGRAPHER,
        self::VOICE_NOTE,
        self::INVITATION,
    ];

    public static function labels(): array
    {
        return [
            self::PHOTOBOOTH => 'Photobooth Online',
            self::GUEST_PHOTOGRAPHER => 'Guest Photographer',
            self::VOICE_NOTE => 'Voice Note Ucapan',
            self::INVITATION => 'Undangan Digital',
        ];
    }

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'module_key',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
