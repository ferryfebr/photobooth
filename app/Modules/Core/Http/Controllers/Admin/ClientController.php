<?php

namespace App\Modules\Core\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Core\Http\Requests\StoreClientRequest;
use App\Modules\Core\Http\Requests\UpdateClientRequest;
use App\Modules\Core\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        return view('admin.clients.index', [
            'clients' => Client::withCount('events')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        Client::create($data);

        return redirect()
            ->route('admin.clients.index')
            ->with('status', 'Client berhasil ditambahkan.');
    }

    public function edit(Client $client): View
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $client->update($data);

        return redirect()
            ->route('admin.clients.index')
            ->with('status', 'Client berhasil diperbarui.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        if ($client->events()->exists()) {
            return back()->with('error', 'Client masih punya event. Hapus event terlebih dahulu.');
        }

        $client->delete();

        return redirect()
            ->route('admin.clients.index')
            ->with('status', 'Client berhasil dihapus.');
    }
}
