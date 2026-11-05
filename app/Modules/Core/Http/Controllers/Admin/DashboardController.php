<?php

namespace App\Modules\Core\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\Client;
use App\Modules\Core\Models\Event;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'clientCount' => Client::count(),
            'eventCount' => Event::count(),
            'activeEventCount' => Event::where('status', 'active')->count(),
            'recentEvents' => Event::with('client')->latest()->limit(5)->get(),
        ]);
    }
}
