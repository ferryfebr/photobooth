@extends('admin.layouts.app')

@section('title', 'Edit Event')

@section('content')
    <h1 class="text-2xl font-semibold">Edit Event</h1>

    <form method="POST" action="{{ route('admin.events.update', $event) }}" class="mt-6 max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @method('PUT')
        @include('admin.events._form', ['event' => $event])
    </form>
@endsection
