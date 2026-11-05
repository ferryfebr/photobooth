<?php

namespace App\Modules\GuestPhotographer\Events;

use App\Modules\GuestPhotographer\Models\GuestPhoto;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GuestPhotoUploaded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public GuestPhoto $photo) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('event.'.$this->photo->event_id);
    }

    public function broadcastAs(): string
    {
        return 'guest-photo.uploaded';
    }
}
