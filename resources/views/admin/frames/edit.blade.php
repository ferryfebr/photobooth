@extends('admin.layouts.app')

@section('title', 'Edit Frame')

@section('content')
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-sm text-slate-500">{{ $event->name }}</p>
            <h1 class="text-2xl font-semibold">Edit Frame</h1>
        </div>
        <a href="{{ route('admin.events.show', $event) }}" class="text-sm text-slate-600 hover:underline">Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.frames.update', [$event, $frame]) }}" enctype="multipart/form-data" class="mt-6 max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @method('PUT')
        @include('admin.frames._form', ['frame' => $frame])
    </form>
@endsection
