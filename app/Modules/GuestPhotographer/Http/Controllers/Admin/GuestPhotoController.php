<?php

namespace App\Modules\GuestPhotographer\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\GuestPhotographer\Models\GuestPhoto;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;

class GuestPhotoController extends Controller
{
    public function approve(GuestPhoto $guestPhoto): RedirectResponse
    {
        $guestPhoto->update(['is_approved' => true]);

        return back()->with('status', 'Foto ditampilkan kembali.');
    }

    public function hide(GuestPhoto $guestPhoto): RedirectResponse
    {
        $guestPhoto->update(['is_approved' => false]);

        return back()->with('status', 'Foto disembunyikan dari galeri.');
    }

    public function destroy(GuestPhoto $guestPhoto, MediaStorage $media): RedirectResponse
    {
        $media->delete($guestPhoto->file_path);
        $guestPhoto->delete();

        return back()->with('status', 'Foto dihapus.');
    }
}
