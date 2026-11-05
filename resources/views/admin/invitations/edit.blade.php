@extends('admin.layouts.app')

@section('title', 'Undangan ' . $event->name)

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-slate-500">Undangan Digital</p>
            <h1 class="text-2xl font-semibold">{{ $event->name }}</h1>
        </div>
        <a href="{{ route('invitation.show', $event->slug) }}" target="_blank" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">
            Preview Undangan
        </a>
    </div>

    <form method="POST" action="{{ route('admin.invitations.update', $event) }}" enctype="multipart/form-data" class="mt-6 max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')

        <div class="grid gap-4">
            <div>
                <label for="headline" class="mb-1 block text-sm font-medium">Judul Undangan</label>
                <input id="headline" name="headline" type="text" value="{{ old('headline', $detail?->headline) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
                @error('headline')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="mb-1 block text-sm font-medium">Deskripsi Singkat</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">{{ old('description', $detail?->description) }}</textarea>
                @error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="story" class="mb-1 block text-sm font-medium">Cerita</label>
                <textarea id="story" name="story" rows="5"
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">{{ old('story', $detail?->story) }}</textarea>
                @error('story')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="venue_name" class="mb-1 block text-sm font-medium">Nama Lokasi</label>
                    <input id="venue_name" name="venue_name" type="text" value="{{ old('venue_name', $detail?->venue_name) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
                </div>
                <div>
                    <label for="maps_url" class="mb-1 block text-sm font-medium">URL Google Maps</label>
                    <input id="maps_url" name="maps_url" type="url" value="{{ old('maps_url', $detail?->maps_url) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
                    @error('maps_url')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="venue_address" class="mb-1 block text-sm font-medium">Alamat Lokasi</label>
                <textarea id="venue_address" name="venue_address" rows="2"
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">{{ old('venue_address', $detail?->venue_address) }}</textarea>
            </div>

            <div>
                <label for="gift_info" class="mb-1 block text-sm font-medium">Amplop Digital (opsional)</label>
                <textarea id="gift_info" name="gift_info" rows="3"
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">{{ old('gift_info', $detail?->gift_info) }}</textarea>
            </div>

            <div>
                <label for="cover_image" class="mb-1 block text-sm font-medium">Cover Undangan</label>
                @if ($coverUrl)
                    <img src="{{ $coverUrl }}" alt="Cover" class="mb-2 h-32 rounded-lg object-cover">
                @endif
                <input id="cover_image" name="cover_image" type="file" accept="image/*"
                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('cover_image')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="gallery_images" class="mb-1 block text-sm font-medium">Galeri Foto (bisa pilih beberapa)</label>
                @if ($galleryUrls->isNotEmpty())
                    <div class="mb-2 grid grid-cols-4 gap-2">
                        @foreach ($galleryUrls as $url)
                            <img src="{{ $url }}" alt="Galeri" class="aspect-square rounded-lg object-cover">
                        @endforeach
                    </div>
                @endif
                <input id="gallery_images" name="gallery_images[]" type="file" accept="image/*" multiple
                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('gallery_images.*')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Simpan Undangan
            </button>
            <a href="{{ route('admin.events.show', $event) }}" class="text-sm text-slate-600 hover:underline">Kembali</a>
        </div>
    </form>
@endsection
