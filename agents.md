# AGENTS.md — Panduan untuk AI Coding Agent

Dokumen ini adalah instruksi utama untuk AI agent (Claude Code, Cursor, Copilot Agent, dsb) yang bekerja di repository ini. Baca dokumen ini SEBELUM mulai menulis kode apapun. Baca juga `prd.md`, `structure.md`, `mvc.md`, `security.md`, `features.md`, dan `database-schema.md` sebagai referensi pendukung.

## 1. Ringkasan Project

Sistem SaaS multi-modul berbasis "Event" untuk industri wedding/birthday/corporate event di Indonesia. Terdiri dari 4 modul utama (Photobooth Online, Guest Photographer, Voice Note Ucapan, Undangan Digital) yang berjalan di atas 1 platform inti yang sama. Lihat `prd.md` untuk detail bisnis lengkap.

## 2. Tech Stack Wajib

Jangan mengganti stack ini tanpa persetujuan eksplisit dari saya (pemilik project):

- **Backend**: Laravel (versi terbaru stabil)
- **Database**: MySQL
- **Queue & Cache**: Redis + Laravel Horizon
- **Real-time**: Laravel Reverb (WebSocket) — bukan Pusher, bukan third-party lain
- **Frontend interaktif**: Livewire + Alpine.js (hindari React/Vue kecuali disebutkan lain)
- **Image processing**: Intervention Image
- **Audio processing**: FFmpeg (dipanggil via Symfony Process dari Job)
- **Storage**: Laravel Filesystem dengan driver S3-compatible (Cloudflare R2), local disk hanya untuk development
- **QR Code**: package `simple-qrcode`
- **Environment lokal**: Laragon (Windows)

## 3. Prinsip Kerja Agent

1. **Modular by design.** Setiap modul (Photobooth, GuestPhotographer, VoiceNote, Invitation) harus punya batas yang jelas — model, controller, service, dan view masing-masing tidak boleh saling bergantung langsung. Komunikasi antar modul (jika perlu) lewat Event/Listener, bukan pemanggilan langsung antar Controller.
2. **Tidak ada payment gateway otomatis di V1.** Pembayaran client terjadi manual di luar sistem (via WhatsApp). Admin panel yang provisioning akun & event secara manual. Jangan bangun fitur checkout/invoice otomatis kecuali diminta eksplisit.
3. **Tema undangan & frame photobooth adalah data, bukan hardcoded.** Tema disimpan sebagai view Blade terpisah dengan `theme_slug`, di-assign lewat admin panel. Frame disimpan sebagai file PNG + koordinat JSON, di-upload dan diatur sepenuhnya lewat admin panel tanpa perlu deploy ulang kode.
4. **Semua endpoint publik untuk tamu (scan QR) harus rate-limited** dan divalidasi lewat signed URL atau token unik per event — lihat `security.md`.
5. **Proses berat wajib di background job**, jangan pernah dijalankan sinkron di request-response cycle: compress foto, convert audio, generate ZIP arsip, kirim notifikasi.
6. **Composing foto+frame diprioritaskan di frontend (Canvas API)** untuk mengurangi beban server; fallback backend processing (Intervention Image) hanya untuk finalisasi/watermark jika diperlukan.
7. **Mobile-first.** Mayoritas tamu mengakses lewat HP saat scan QR — semua halaman publik wajib dites di viewport mobile dulu.
8. **Tidak menyimpan kredensial/API key di kode.** Semua secret di `.env`, jangan pernah commit `.env` ke git.

## 4. Konvensi Kode

- Ikuti **PSR-12** dan konvensi standar Laravel (nama model singular PascalCase, tabel plural snake_case, dsb).
- Nama route mengikuti pola `modul.aksi` (misal `photobooth.capture`, `invitation.rsvp`).
- Setiap fitur baru wajib disertai **Form Request** untuk validasi input, jangan validasi inline di controller untuk input kompleks.
- Gunakan **Policy** untuk otorisasi (misal: client hanya bisa akses event miliknya sendiri).
- Query kompleks/berulang dipindah ke method di Model atau Query Scope, bukan ditulis ulang di tiap controller.
- Setiap Job wajib punya `$tries` dan `failed()` method untuk menangani kegagalan (misal convert audio gagal karena file corrupt).

## 5. Testing

- Fitur inti (generate QR, upload foto, compose frame, convert audio, RSVP) wajib punya Feature Test minimal happy-path.
- Job/queue wajib ditest dengan `Queue::fake()`.
- Broadcasting/Reverb events wajib ditest dengan `Event::fake()`.

## 6. Yang TIDAK Boleh Dilakukan Agent Tanpa Konfirmasi

- Mengubah tech stack inti (lihat poin 2).
- Menambahkan dependency/package baru yang tidak esensial tanpa menyebutkan alasannya ke saya dulu.
- Membuat sistem billing/payment otomatis.
- Menghapus atau mengubah struktur tabel yang sudah dipakai modul lain tanpa migration yang aman (gunakan `nullable`, jangan drop kolom yang masih dipakai).
- Menyimpan file media (foto/audio) di local disk untuk environment production — wajib ke S3-compatible storage.

## 7. Urutan Development yang Disarankan

Lihat `tasks.md` untuk breakdown fase lengkap. Ringkasnya: bangun Core (auth, event, admin panel) → Modul Undangan Digital → Modul Photobooth → Modul Guest Photographer → Modul VN → integrasi lintas modul → polish & security hardening.
