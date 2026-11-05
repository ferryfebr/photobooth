# STRUCTURE.md — Struktur Folder Project

Pendekatan: **modular monolith** dalam 1 Laravel app (bukan microservices, bukan multi-repo). Setiap modul punya folder sendiri di dalam `app/Modules` supaya batasnya jelas, tapi tetap 1 aplikasi, 1 database, 1 deploy.

```
project-root/
├── app/
│   ├── Modules/
│   │   ├── Core/
│   │   │   ├── Models/            (Client, Event, Package, EventModule, AdminUser)
│   │   │   ├── Http/Controllers/  (Admin\EventController, Admin\ClientController, ...)
│   │   │   ├── Http/Requests/
│   │   │   ├── Services/          (QrCodeGeneratorService, ThemeResolverService)
│   │   │   ├── Policies/
│   │   │   └── routes.php
│   │   │
│   │   ├── Photobooth/
│   │   │   ├── Models/            (PhotoboothSession, PhotoboothPhoto, Frame)
│   │   │   ├── Http/Controllers/
│   │   │   ├── Services/          (PhotoComposerService)
│   │   │   ├── Jobs/              (CompressPhotoJob)
│   │   │   └── routes.php
│   │   │
│   │   ├── GuestPhotographer/
│   │   │   ├── Models/            (GuestPhoto)
│   │   │   ├── Http/Controllers/
│   │   │   ├── Events/            (GuestPhotoUploaded — broadcast ke Reverb)
│   │   │   ├── Jobs/              (CompressGuestPhotoJob)
│   │   │   └── routes.php
│   │   │
│   │   ├── VoiceNote/
│   │   │   ├── Models/            (VoiceNote)
│   │   │   ├── Http/Controllers/
│   │   │   ├── Jobs/              (ConvertAudioJob)
│   │   │   └── routes.php
│   │   │
│   │   └── Invitation/
│   │       ├── Models/            (InvitationTheme, Rsvp)
│   │       ├── Http/Controllers/
│   │       └── routes.php
│   │
│   └── Support/                   (helper generik lintas modul, mis. ZipArchiveHelper)
│
├── resources/
│   └── views/
│       ├── admin/                 (view untuk admin panel)
│       ├── invitations/
│       │   ├── default/           (fallback theme)
│       │   ├── {theme_slug}/      (1 folder per event custom, mis. wedding-siti-budi-2026)
│       │   └── ...
│       ├── photobooth/
│       ├── guest-photographer/
│       └── voice-note/
│
├── routes/
│   └── web.php                    (include semua Modules/*/routes.php)
│
├── database/
│   └── migrations/                (lihat database-schema.md untuk detail tabel)
│
├── storage/
│   └── app/private-local-dev/     (HANYA untuk local dev, production wajib S3-compatible)
│
├── docs/                          (folder ini — agents.md, prd.md, dst)
│
└── .env                           (JANGAN commit ke git)
```

## Catatan Routing Subdomain

```php
// routes/web.php
Route::domain('{event_slug}.namasistem.com')->group(function () {
    // load routes dari Modules/Invitation/routes.php, Photobooth/routes.php, dst
    // {event_slug} di-resolve ke model Event lewat Route Model Binding custom
});

Route::domain('namasistem.com')->group(function () {
    // landing page utama, admin panel
});
```

## Penamaan File Tema & Frame

- Tema: `resources/views/invitations/{theme_slug}/index.blade.php` — `theme_slug` di-generate dari nama client + random string pendek, disimpan di kolom `events.theme_slug`.
- Frame: file fisik disimpan di S3-compatible storage (bukan di repo), path disimpan di kolom `frames.file_path`, koordinat slot foto disimpan sebagai JSON di kolom `frames.slot_coordinates`.
