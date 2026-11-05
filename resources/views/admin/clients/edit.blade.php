@extends('admin.layouts.app')

@section('title', 'Edit Client')

@section('content')
    <h1 class="text-2xl font-semibold">Edit Client</h1>

    <form method="POST" action="{{ route('admin.clients.update', $client) }}" class="mt-6 max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        @method('PUT')
        @include('admin.clients._form', ['client' => $client])
    </form>
@endsection
