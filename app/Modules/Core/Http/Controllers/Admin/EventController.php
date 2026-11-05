<?php

namespace App\Modules\Core\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Core\Http\Requests\StoreEventRequest;
use App\Modules\Core\Http\Requests\UpdateEventRequest;
use App\Modules\Core\Jobs\GenerateArchiveZipJob;
use App\Modules\Core\Models\Client;
use App\Modules\Core\Models\Event;
use App\Modules\Core\Models\EventModule;
use App\Modules\Core\Models\Package;
use App\Modules\Core\Services\QrCodeGeneratorService;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    public function index(): View
    {
        return view('admin.events.index', [
            'events' => Event::with(['client', 'package'])->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.events.create', $this->formData());
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $event = Event::create($this->eventAttributes($data) + [
            'slug' => Event::generateUniqueSlug($data['name']),
        ]);

        $this->syncModules($event, $this->selectedModules($request, $data));

        return redirect()
            ->route('admin.events.show', $event)
            ->with('status', 'Event berhasil dibuat.');
    }

    public function show(Event $event): View
    {
        $event->load([
            'client',
            'package',
            'modules',
            'invitationDetail',
            'frames',
            'photoboothSessions.photos',
            'guestPhotos' => fn ($query) => $query->latest()->limit(48),
            'voiceNotes' => fn ($query) => $query->latest()->limit(50),
            'rsvps' => fn ($query) => $query->latest()->limit(100),
            'activityLogs' => fn ($query) => $query->latest()->limit(200),
        ]);

        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        $event->load('modules');

        return view('admin.events.edit', $this->formData() + compact('event'));
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();
        $event->update($this->eventAttributes($data));

        if ($request->boolean('modules_submitted') || array_key_exists('modules', $data)) {
            $this->syncModules($event, $data['modules'] ?? []);
        }

        return redirect()
            ->route('admin.events.show', $event)
            ->with('status', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('status', 'Event berhasil dihapus.');
    }

    public function archive(Event $event): RedirectResponse
    {
        $cacheKey = 'archive:event:'.$event->id;

        Cache::forget($cacheKey);
        GenerateArchiveZipJob::dispatch($event->id, $cacheKey);

        return back()->with('status', 'Arsip sedang dibuat di background. Muat ulang halaman beberapa saat lagi.');
    }

    public function archiveDownload(Event $event, MediaStorage $media): StreamedResponse|RedirectResponse
    {
        $path = Cache::get('archive:event:'.$event->id);

        if (! $path || ! Storage::disk($media->disk())->exists($path)) {
            return back()->with('error', 'Arsip belum siap. Klik "Buat Arsip" terlebih dahulu.');
        }

        return Storage::disk($media->disk())->download($path, 'arsip-'.$event->slug.'.zip');
    }

    public function qr(Event $event, QrCodeGeneratorService $qrCode): Response
    {
        $svg = $qrCode->svg($event->publicUrl());

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="qr-'.$event->slug.'.svg"',
        ]);
    }

    private function formData(): array
    {
        return [
            'clients' => Client::orderBy('name')->get(),
            'packages' => Package::orderBy('name')->get(),
            'moduleKeys' => EventModule::MODULE_KEYS,
        ];
    }

    private function eventAttributes(array $data): array
    {
        $package = isset($data['package_id'])
            ? Package::find($data['package_id'])
            : null;

        $storageDays = $data['storage_days']
            ?? $package?->default_storage_days
            ?? 90;

        return [
            'client_id' => $data['client_id'],
            'package_id' => $data['package_id'] ?? null,
            'name' => $data['name'],
            'theme_slug' => $data['theme_slug'] ?? null,
            'event_date' => $data['event_date'],
            'photobooth_active_from' => $data['photobooth_active_from'] ?? null,
            'photobooth_active_until' => $data['photobooth_active_until'] ?? null,
            'storage_expires_at' => Carbon::parse($data['event_date'])->addDays($storageDays),
            'status' => $data['status'],
        ];
    }

    private function selectedModules(Request $request, array $data): array
    {
        if ($request->boolean('modules_submitted') || array_key_exists('modules', $data)) {
            return $data['modules'] ?? [];
        }

        if (! empty($data['package_id'])) {
            return Package::find($data['package_id'])?->default_modules ?? [];
        }

        return [];
    }

    private function syncModules(Event $event, array $selected): void
    {
        foreach (EventModule::MODULE_KEYS as $moduleKey) {
            EventModule::updateOrCreate(
                ['event_id' => $event->id, 'module_key' => $moduleKey],
                ['is_active' => in_array($moduleKey, $selected, true)],
            );
        }
    }
}
