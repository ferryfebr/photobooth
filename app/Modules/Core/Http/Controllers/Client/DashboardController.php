<?php

namespace App\Modules\Core\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $client = auth('client')->user();

        return view('client.dashboard', [
            'client' => $client,
            'events' => $client->events()->with(['package', 'modules'])->latest()->get(),
        ]);
    }
}
