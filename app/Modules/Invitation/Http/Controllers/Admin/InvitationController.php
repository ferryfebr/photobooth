<?php

namespace App\Modules\Invitation\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\Event;
use App\Modules\Invitation\Http\Requests\UpdateInvitationRequest;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function edit(Event $event, MediaStorage $media): View
    {
        $event->load('invitationDetail');
        $detail = $event->invitationDetail;

        return view('admin.invitations.edit', [
            'event' => $event,
            'detail' => $detail,
            'coverUrl' => $media->url($detail?->cover_image_path),
            'galleryUrls' => collect($detail?->gallery_images ?? [])
                ->map(fn (string $path) => $media->url($path))
                ->filter()
                ->values(),
        ]);
    }

    public function update(UpdateInvitationRequest $request, Event $event, MediaStorage $media): RedirectResponse
    {
        $data = $request->validated();
        $detail = $event->invitationDetail()->firstOrNew();

        if ($request->hasFile('cover_image')) {
            $media->delete($detail->cover_image_path);
            $data['cover_image_path'] = $media->store($request->file('cover_image'), 'invitations/covers');
        }

        $gallery = $detail->gallery_images ?? [];

        foreach ($request->file('gallery_images', []) as $image) {
            $gallery[] = $media->store($image, 'invitations/gallery');
        }

        $data['gallery_images'] = array_values($gallery);

        $detail->fill(Arr::except($data, ['cover_image', 'gallery_images']) + [
            'gallery_images' => $data['gallery_images'],
        ]);
        $detail->event_id = $event->id;
        $detail->save();

        return redirect()
            ->route('admin.invitations.edit', $event)
            ->with('status', 'Data undangan berhasil disimpan.');
    }
}
