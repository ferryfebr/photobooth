<?php

namespace App\Modules\Invitation\Events;

use App\Modules\Invitation\Models\Rsvp;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RsvpSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Rsvp $rsvp) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('event.'.$this->rsvp->event_id);
    }

    public function broadcastAs(): string
    {
        return 'rsvp.submitted';
    }
}
