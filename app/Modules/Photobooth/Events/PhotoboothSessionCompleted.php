<?php

namespace App\Modules\Photobooth\Events;

use App\Modules\Photobooth\Models\PhotoboothSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhotoboothSessionCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public PhotoboothSession $session) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('event.'.$this->session->event_id);
    }

    public function broadcastAs(): string
    {
        return 'photobooth.completed';
    }
}
