<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
            <a href="{{ route('client.dashboard') }}" class="text-lg font-semibold">
                {{ config('app.name') }} <span class="text-slate-400">Client</span>
            </a>
            <nav class="flex items-center gap-2 text-sm">
                <span class="hidden text-slate-500 sm:inline">{{ auth('client')->user()?->name }}</span>
                <form method="POST" action="{{ route('client.logout') }}">
                    @csrf
                    <button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-slate-600 hover:bg-slate-50">Keluar</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('status'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
