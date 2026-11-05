<?php

namespace App\Modules\Photobooth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\Event;
use App\Modules\Photobooth\Http\Requests\StoreFrameRequest;
use App\Modules\Photobooth\Models\Frame;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FrameController extends Controller
{
    public function create(Event $event): View
    {
        return view('admin.frames.create', compact('event'));
    }

    public function store(StoreFrameRequest $request, Event $event, MediaStorage $media): RedirectResponse
    {
        $data = $request->validated();

        Frame::create([
            'event_id' => $event->id,
            'name' => $data['name'],
            'file_path' => $media->store($request->file('frame'), 'frames'),
            'slot_coordinates' => $this->slots($data['slots']),
            'photo_count' => $data['photo_count'],
        ]);

        return redirect()
            ->route('admin.events.show', $event)
            ->with('status', 'Frame berhasil ditambahkan.');
    }

    public function edit(Event $event, Frame $frame, MediaStorage $media): View
    {
        abort_unless($frame->event_id === $event->id, 404);

        return view('admin.frames.edit', [
            'event' => $event,
            'frame' => $frame,
            'frameUrl' => $media->url($frame->file_path),
        ]);
    }

    public function update(StoreFrameRequest $request, Event $event, Frame $frame, MediaStorage $media): RedirectResponse
    {
        abort_unless($frame->event_id === $event->id, 404);

        $data = $request->validated();
        $attributes = [
            'name' => $data['name'],
            'slot_coordinates' => $this->slots($data['slots']),
            'photo_count' => $data['photo_count'],
        ];

        if ($request->hasFile('frame')) {
            $media->delete($frame->file_path);
            $attributes['file_path'] = $media->store($request->file('frame'), 'frames');
        }

        $frame->update($attributes);

        return redirect()
            ->route('admin.events.show', $event)
            ->with('status', 'Frame berhasil diperbarui.');
    }

    public function destroy(Event $event, Frame $frame, MediaStorage $media): RedirectResponse
    {
        abort_unless($frame->event_id === $event->id, 404);

        $media->delete($frame->file_path);
        $frame->delete();

        return redirect()
            ->route('admin.events.show', $event)
            ->with('status', 'Frame berhasil dihapus.');
    }

    private function slots(array $slots): array
    {
        return collect($slots)->map(fn (array $slot) => [
            'x' => (float) $slot['x'],
            'y' => (float) $slot['y'],
            'width' => (float) $slot['width'],
            'height' => (float) $slot['height'],
        ])->values()->all();
    }
}
