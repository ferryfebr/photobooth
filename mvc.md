# MVC.md — Arsitektur Aplikasi

Pola dasar: Laravel MVC standar + Service Layer + Jobs/Events untuk proses async. Lihat `structure.md` untuk lokasi file fisik tiap komponen.

## 1. Model & Relasi (Ringkasan — detail lengkap di `database-schema.md`)

```
Client 1---N Event
Event 1---N EventModule (pivot: modul apa saja aktif utk event ini)
Event 1---1 InvitationTheme (via theme_slug)
Event 1---N PhotoboothSession 1---N PhotoboothPhoto
Event 1---N Frame (frame yang di-assign ke event ini)
Event 1---N GuestPhoto
Event 1---N VoiceNote
Event 1---N Rsvp
AdminUser (terpisah dari Client — untuk akses admin panel)
```

## 2. Controller per Modul

| Modul | Controller | Tanggung Jawab |
|---|---|---|
| Core | `Admin\EventController` | CRUD event, assign tema/frame, set tanggal aktif modul |
| Core | `Admin\ClientController` | CRUD data client (manual provisioning) |
| Core | `Admin\FrameController` | Upload frame baru, set koordinat slot |
| Photobooth | `PhotoboothController` | Tampilkan landing, terima capture, proses compose |
| GuestPhotographer | `GuestPhotographerController` | Terima upload foto tamu, tampilkan galeri live |
| VoiceNote | `VoiceNoteController` | Terima rekaman, tampilkan kotak ucapan |
| Invitation | `InvitationController` | Render undangan sesuai tema, terima RSVP |

## 3. Service Layer

- `ThemeResolverService` — resolve `theme_slug` ke path view, fallback ke `default` jika tidak ditemukan.
- `QrCodeGeneratorService` — generate QR per event/modul, wrapping package `simple-qrcode`.
- `PhotoComposerService` — logic fallback compose foto+frame di backend (kalau frontend Canvas gagal/tidak didukung browser tertentu).
- `FrameCoordinateService` — validasi & simpan koordinat slot foto dari form admin.
- `ArchiveExportService` — generate ZIP arsip semua hasil event untuk didownload client.

## 4. Jobs (Background Queue)

| Job | Trigger | Tugas |
|---|---|---|
| `CompressPhotoJob` | Setelah upload foto photobooth/guest photographer | Resize & compress via Intervention Image |
| `ConvertAudioJob` | Setelah upload VN | Convert webm → mp3 via FFmpeg |
| `GenerateArchiveZipJob` | Client klik "Download Arsip" | Kumpulkan semua file event jadi 1 ZIP, simpan link sementara |
| `PurgeExpiredEventDataJob` | Terjadwal (scheduler harian) | Hapus/arsip data event yang sudah lewat masa penyimpanan sesuai paket |

## 5. Events & Broadcasting (Laravel Reverb)

| Event | Broadcast ke | Dipakai untuk |
|---|---|---|
| `GuestPhotoUploaded` | Channel privat per event | Update galeri live di dashboard client tanpa reload |
| `RsvpSubmitted` | Channel privat per event | Update rekap RSVP real-time di dashboard client |
| `VoiceNoteReceived` | Channel privat per event | Notifikasi "kotak ucapan" baru masuk |

## 6. Policy (Otorisasi)

- `EventPolicy` — client hanya bisa lihat/edit event miliknya sendiri (kalau client punya login).
- `FramePolicy`, `ThemePolicy` — hanya admin yang bisa CRUD.
- Endpoint publik (photobooth, guest photographer, VN, RSVP) **tidak pakai auth Laravel biasa** — validasi lewat signed URL/token per event, lihat `security.md`.

## 7. Form Requests (Validasi)

- `StorePhotoboothCaptureRequest`, `StoreGuestPhotoRequest`, `StoreVoiceNoteRequest`, `StoreRsvpRequest`, `StoreFrameRequest`, `StoreEventRequest` — semua validasi kompleks wajib lewat sini, bukan inline di controller.
