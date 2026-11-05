<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Models\ActivityLog as EventActivityLog;
use App\Modules\GuestPhotographer\Models\GuestPhoto;
use App\Modules\Invitation\Models\InvitationDetail;
use App\Modules\Invitation\Models\Rsvp;
use App\Modules\Photobooth\Models\Frame;
use App\Modules\Photobooth\Models\PhotoboothSession;
use App\Modules\VoiceNote\Models\VoiceNote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'client_id',
        'package_id',
        'name',
        'slug',
        'theme_slug',
        'event_date',
        'photobooth_active_from',
        'photobooth_active_until',
        'storage_expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'photobooth_active_from' => 'datetime',
            'photobooth_active_until' => 'datetime',
            'storage_expires_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(EventModule::class);
    }

    public function invitationDetail(): HasOne
    {
        return $this->hasOne(InvitationDetail::class);
    }

    public function frames(): HasMany
    {
        return $this->hasMany(Frame::class);
    }

    public function photoboothSessions(): HasMany
    {
        return $this->hasMany(PhotoboothSession::class);
    }

    public function guestPhotos(): HasMany
    {
        return $this->hasMany(GuestPhoto::class);
    }

    public function voiceNotes(): HasMany
    {
        return $this->hasMany(VoiceNote::class);
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(EventActivityLog::class);
    }

    public function isModuleActive(string $moduleKey): bool
    {
        return $this->modules
            ->where('module_key', $moduleKey)
            ->where('is_active', true)
            ->isNotEmpty();
    }

    public function photoboothIsOpen(): bool
    {
        $now = now();

        if ($this->photobooth_active_from && $now->lt($this->photobooth_active_from)) {
            return false;
        }

        if ($this->photobooth_active_until && $now->gt($this->photobooth_active_until)) {
            return false;
        }

        return true;
    }

    public static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'event';

        do {
            $slug = $base.'-'.Str::lower(Str::random(5));
        } while (static::where('slug', $slug)->exists());

        return $slug;
    }

    public function publicUrl(): string
    {
        $domain = config('app.event_domain');

        if ($domain) {
            $scheme = app()->environment('local') ? 'http' : 'https';

            return $scheme.'://'.$this->slug.'.'.$domain;
        }

        return route('events.landing', $this->slug);
    }
}
