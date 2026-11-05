<?php

namespace App\Modules\Photobooth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoboothPhoto extends Model
{
    protected $fillable = [
        'photobooth_session_id',
        'file_path',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PhotoboothSession::class, 'photobooth_session_id');
    }
}
