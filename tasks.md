# TASKS.md — Roadmap & Breakdown Tugas

Urutan ini dirancang supaya Anda punya sesuatu yang bisa didemo secepat mungkin (untuk pitching ke client pertama), sebelum semua modul selesai 100%.

## Fase 0 — Setup Project (Fondasi)

- [ ] Install Laravel terbaru di Laragon
- [ ] Setup MySQL database lokal
- [ ] Setup Redis lokal (untuk queue & cache)
- [ ] Install & konfigurasi Laravel Horizon
- [ ] Install & konfigurasi Laravel Reverb
- [ ] Setup struktur folder modular sesuai `structure.md`
- [ ] Buat migration dasar: `admin_users`, `clients`, `packages`, `events`, `event_modules` (lihat `database-schema.md`)
- [ ] Setup Cloudflare Tunnel/ngrok untuk testing akses dari HP/demo ke calon client

## Fase 1 — Core & Admin Panel (MVP Fondasi)

- [ ] Auth admin panel (login, 2FA opsional)
- [ ] CRUD Client (manual input data client setelah deal via WA)
- [ ] CRUD Event (buat event, set tanggal aktif, generate slug unik)
- [ ] Generate QR code otomatis per event
- [ ] Routing subdomain dinamis (`{event_slug}.namasistem.com`)
- [ ] Landing page publik per event (menu pilihan modul — boleh masih placeholder untuk modul yang belum jadi)

## Fase 2 — Modul Undangan Digital (Prioritas Pertama)

Alasan diprioritaskan: modul ini paling sering jadi "pintu masuk" client dan paling mudah didemo untuk pitching awal.

- [ ] Migration `invitation_themes`, `rsvps`
- [ ] Form input data acara di admin panel
- [ ] Theme Engine: render view sesuai `theme_slug`, fallback ke `default`
- [ ] Halaman undangan publik + RSVP form
- [ ] Broadcast `RsvpSubmitted` ke dashboard client (real-time)
- [ ] Rekap RSVP di dashboard client
- [ ] Bikin 1 tema `default` sebagai starter (Anda desain sendiri dulu, tanpa bayar designer)

## Fase 3 — Modul Photobooth Online

- [ ] Migration `frames`, `photobooth_sessions`, `photobooth_photos`
- [ ] Form upload frame + set koordinat slot di admin panel
- [ ] Halaman capture: akses kamera, countdown, batas jumlah foto
- [ ] Compose foto+frame di frontend (Canvas API)
- [ ] Job `CompressPhotoJob`
- [ ] Download hasil akhir

## Fase 4 — Modul Guest Photographer

- [ ] Migration `guest_photos`
- [ ] Halaman "Jadi Fotografer" (reuse komponen kamera dari Fase 3 jika memungkinkan)
- [ ] Upload otomatis + Job `CompressGuestPhotoJob`
- [ ] Broadcast `GuestPhotoUploaded` ke galeri live dashboard client
- [ ] Moderasi (hide/approve) di dashboard client
- [ ] Download arsip ZIP

## Fase 5 — Modul Voice Note Ucapan

- [ ] Migration `voice_notes`
- [ ] Halaman rekam VN (`MediaRecorder API`)
- [ ] Job `ConvertAudioJob` (FFmpeg webm → mp3)
- [ ] Broadcast `VoiceNoteReceived`
- [ ] Kotak ucapan di dashboard client dengan audio player

## Fase 6 — Integrasi Lintas Modul & Polish

- [ ] Integrasi tombol semua modul dalam 1 halaman undangan
- [ ] `ArchiveExportService` — generate ZIP arsip lengkap per event
- [ ] `PurgeExpiredEventDataJob` — scheduler hapus data expired
- [ ] Rate limiting semua endpoint publik (lihat `security.md`)
- [ ] Signed URL untuk semua aksi submit tamu
- [ ] Testing beban (load test) simulasi traffic spike

## Fase 7 — Pre-Launch (Sebelum Client Pertama Bayar)

- [ ] Setup domain + VPS Lite (sesuai kesepakatan modal di `prd.md`)
- [ ] Setup wildcard SSL
- [ ] Setup S3-compatible storage (Cloudflare R2)
- [ ] Setup uptime monitoring
- [ ] Backup rutin database terjadwal
- [ ] Demo internal end-to-end sebelum dipakai client sungguhan (acara kecil/keluarga sebagai portofolio pertama)

## Fase 8 — Fase Lanjutan (Setelah Bisnis Berjalan)

- [ ] Payment gateway otomatis (Midtrans/Xendit) jika volume client sudah tinggi
- [ ] Katalog tema template siap pilih (opsi tambahan selain custom)
- [ ] Sistem lisensi untuk model bisnis jual sebagai template/source code
- [ ] Upgrade VPS sesuai kebutuhan traffic (lihat opsi di `prd.md`)
