@extends('admin.layouts.app')

@section('title', 'Event')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Event</h1>
            <p class="mt-1 text-sm text-slate-500">Satu event mewakili satu acara client.</p>
        </div>
        <a href="{{ route('admin.events.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
            Buat Event
        </a>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
        @if ($events->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">Belum ada event.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Event</th>
                            <th class="px-5 py-3">Client</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($events as $event)
                            <tr>
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.events.show', $event) }}" class="font-medium hover:underline">{{ $event->name }}</a>
                                    <p class="text-xs text-slate-500">{{ $event->slug }}</p>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $event->client?->name }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $event->event_date?->format('d M Y') }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">{{ $event->status }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.events.qr', $event) }}" target="_blank" class="rounded-md border border-slate-300 px-2.5 py-1.5 text-xs font-medium hover:bg-slate-50">
                                            QR
                                        </a>
                                        <a href="{{ route('admin.events.show', $event) }}" class="rounded-md border border-slate-300 px-2.5 py-1.5 text-xs font-medium hover:bg-slate-50">
                                            Detail
                                        </a>
                                        <a href="{{ route('admin.events.edit', $event) }}" class="rounded-md border border-slate-300 px-2.5 py-1.5 text-xs font-medium hover:bg-slate-50">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-3">
                {{ $events->links() }}
            </div>
        @endif
    </div>
@endsection
