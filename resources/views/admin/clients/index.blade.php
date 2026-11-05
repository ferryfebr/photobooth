@extends('admin.layouts.app')

@section('title', 'Client')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Client</h1>
            <p class="mt-1 text-sm text-slate-500">Data client diinput manual setelah deal via WhatsApp.</p>
        </div>
        <a href="{{ route('admin.clients.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
            Tambah Client
        </a>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
        @if ($clients->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">Belum ada client.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Kontak</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Event</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($clients as $client)
                            <tr>
                                <td class="px-5 py-3">
                                    <p class="font-medium">{{ $client->name }}</p>
                                    @if ($client->email)
                                        <p class="text-xs text-slate-500">{{ $client->email }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $client->phone }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $client->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $client->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $client->events_count }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.clients.edit', $client) }}" class="rounded-md border border-slate-300 px-2.5 py-1.5 text-xs font-medium hover:bg-slate-50">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.clients.destroy', $client) }}" onsubmit="return confirm('Hapus client ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-rose-200 px-2.5 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-3">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
@endsection
