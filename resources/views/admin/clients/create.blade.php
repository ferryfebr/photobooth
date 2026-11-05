@extends('admin.layouts.app')

@section('title', 'Tambah Client')

@section('content')
    <h1 class="text-2xl font-semibold">Tambah Client</h1>

    <form method="POST" action="{{ route('admin.clients.store') }}" class="mt-6 max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        @include('admin.clients._form', ['client' => null])
    </form>
@endsection
