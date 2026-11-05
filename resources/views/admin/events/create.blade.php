@extends('admin.layouts.app')

@section('title', 'Buat Event')

@section('content')
    <h1 class="text-2xl font-semibold">Buat Event</h1>

    <form method="POST" action="{{ route('admin.events.store') }}" class="mt-6 max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('admin.events._form', ['event' => null])
    </form>
@endsection
