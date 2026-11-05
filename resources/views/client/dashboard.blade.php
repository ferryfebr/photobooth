@extends('client.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-2xl font-semibold">Halo, {{ $client->name }}</h1>
    <p class="mt-1 text-sm text-slate-500">Pantau hasil acara kamu di sini.</p>

    @if ($events->isEmpty())
        <div class="mt-6 rounded-xl border border-slate-200 bg-white px-5 py-10 text-center text-sm text-slate-500">
            Belum ada event yang terhubung ke akunmu.
        </div>
    @else
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @foreach ($events as $event)
                <a href="{{ route('client.events.show', $event) }}" class="group rounded-xl border border-slate-200 bg-white p-5 transition hover:border-slate-400">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">{{ $event->name }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $event->event_date?->format('d M Y') }} · {{ $event->package?->name ?? 'Tanpa paket' }}</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ $event->status }}</span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs">
                        @foreach ($event->modules->where('is_active', true) as $module)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700">
                                {{ \App\Modules\Core\Models\EventModule::labels()[$module->module_key] ?? $module->module_key }}
                            </span>
                        @endforeach
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
