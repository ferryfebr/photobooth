@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-2xl font-semibold">Dashboard</h1>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Total Client</p>
            <p class="mt-2 text-3xl font-semibold">{{ $clientCount }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Total Event</p>
            <p class="mt-2 text-3xl font-semibold">{{ $eventCount }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Event Aktif</p>
            <p class="mt-2 text-3xl font-semibold">{{ $activeEventCount }}</p>
        </div>
    </div>

    <div class="mt-8 rounded-xl border border-slate-200 bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold">Event Terbaru</h2>
            <a href="{{ route('admin.events.create') }}" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">
                Buat Event
            </a>
        </div>

        @if ($recentEvents->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-slate-500">Belum ada event.</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($recentEvents as $event)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3">
                        <div>
                            <a href="{{ route('admin.events.show', $event) }}" class="font-medium hover:underline">{{ $event->name }}</a>
                            <p class="text-sm text-slate-500">{{ $event->client?->name }} · {{ $event->event_date?->format('d M Y') }}</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ $event->status }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
