<?php

namespace App\Modules\Core\Policies;

use App\Modules\Core\Models\Client;
use App\Modules\Core\Models\Event;

class EventPolicy
{
    public function view(Client $client, Event $event): bool
    {
        return $event->client_id === $client->id;
    }
}
