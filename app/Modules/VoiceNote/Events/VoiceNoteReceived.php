<?php

namespace App\Modules\VoiceNote\Events;

use App\Modules\VoiceNote\Models\VoiceNote;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoiceNoteReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public VoiceNote $voiceNote) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('event.'.$this->voiceNote->event_id);
    }

    public function broadcastAs(): string
    {
        return 'voice-note.received';
    }
}
