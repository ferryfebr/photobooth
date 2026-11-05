<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
            <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold text-slate-900">
                {{ config('app.name') }} <span class="text-slate-400">Admin</span>
            </a>

            <nav class="flex flex-wrap items-center gap-1 text-sm font-medium">
                <a href="{{ route('admin.dashboard') }}"
                   class="rounded-md px-3 py-2 {{ request()->routeIs('admin.dashboard') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.clients.index') }}"
                   class="rounded-md px-3 py-2 {{ request()->routeIs('admin.clients.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    Client
                </a>
                <a href="{{ route('admin.events.index') }}"
                   class="rounded-md px-3 py-2 {{ request()->routeIs('admin.events.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    Event
                </a>

                <form method="POST" action="{{ route('admin.logout') }}" class="ml-2">
                    @csrf
                    <button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-slate-600 hover:bg-slate-50">
                        Keluar
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('status'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
