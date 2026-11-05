<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('event.{eventId}', function ($user, $eventId) {
    return $user !== null && (int) $user->id > 0;
}, ['guards' => ['admin', 'client']]);
