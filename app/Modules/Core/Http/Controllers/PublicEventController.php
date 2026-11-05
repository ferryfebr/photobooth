<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\Event;
use Illuminate\View\View;

class PublicEventController extends Controller
{
    public function __invoke(Event $event): View
    {
        abort_unless(auth('admin')->check() || in_array($event->status, ['active', 'completed'], true), 404);

        $event->load(['client', 'modules']);

        return view('events.landing', compact('event'));
    }
}
