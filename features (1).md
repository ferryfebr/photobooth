# FEATURES.md — Daftar Fitur Lengkap

## A. Sistem Inti (Core / Shared)

- [ ] Login admin (Anda) dengan proteksi tambahan (rekomendasi: 2FA)
- [ ] Login client (opsional di V1 — bisa juga client tidak perlu login, cukup admin yang kelola semua dari admin panel)
- [ ] Dashboard client: overview event miliknya, hasil masuk real-time
- [ ] Manajemen event: create, edit, set tanggal aktif per modul, assign tema & frame
- [ ] Generate QR code otomatis per event (dan per modul jika diperlukan QR terpisah)
- [ ] Landing page publik per event (menu pilihan 4 modul dalam 1 halaman)
- [ ] Theme engine: registry tema undangan (`theme_slug` → view Blade)
- [ ] Frame engine: upload PNG frame + set koordinat slot foto (JSON) lewat form admin
- [ ] Manajemen client (data kontak, catatan paket yang dibeli, status aktif/nonaktif)
- [ ] Log aktivitas per event (analytics ringan: berapa tamu pakai fitur apa)
- [ ] Sistem arsip: export ZIP semua hasil (foto + audio) sebelum masa penyimpanan habis
- [ ] Notifikasi (email/WA API) — reminder H-1 ke tamu yang belum RSVP (opsional, fase lanjutan)

## B. Modul Photobooth Online

- [ ] Landing page scan QR, mobile-friendly, tanpa install app
- [ ] Minta izin akses kamera (`getUserMedia`)
- [ ] Countdown sebelum jepret (3-2-1)
- [ ] Batas jumlah foto per sesi (diatur admin per event, default misal 4)
- [ ] Preview & retake sebelum lanjut ke tahap pilih frame
- [ ] Pilihan frame (list frame yang di-assign ke event tsb)
- [ ] Compose otomatis foto + frame (Canvas API di frontend)
- [ ] Download hasil akhir (JPG/PNG)
- [ ] Share langsung ke WhatsApp/Instagram (native share sheet browser jika didukung)
- [ ] Rate limiting per sesi/device untuk cegah abuse

## C. Modul Guest Photographer

- [ ] Menu "Jadi Fotografer" dari landing page event
- [ ] Batas jumlah foto per tamu (default misal maksimal 10)
- [ ] Upload otomatis ke galeri event (background job: compress dulu)
- [ ] Galeri live yang bisa dipantau client real-time (via Laravel Reverb)
- [ ] Moderasi: admin/client bisa hide/approve foto tertentu
- [ ] Download semua foto sebagai ZIP di akhir acara
- [ ] Rate limiting per device/session

## D. Modul Voice Note Ucapan

- [ ] Rekam VN langsung dari browser (`MediaRecorder API`)
- [ ] Batas durasi rekaman (default misal 60 detik)
- [ ] Input nama pengirim (opsional, bisa anonim)
- [ ] Convert otomatis webm → mp3 (background job, FFmpeg)
- [ ] List "kotak ucapan" di dashboard client dengan audio player inline
- [ ] Download semua VN sebagai arsip (ZIP)

## E. Modul Undangan Digital

- [ ] Form input data acara (nama mempelai/ultah, tanggal, lokasi + Google Maps embed, cerita, galeri foto)
- [ ] Render tema custom sesuai `theme_slug` event
- [ ] RSVP form (hadir/tidak hadir, jumlah tamu, catatan)
- [ ] Rekap RSVP real-time di dashboard client
- [ ] Integrasi tombol lintas modul (kirim ucapan, jadi fotografer, ambil foto photobooth) dalam 1 halaman
- [ ] Fitur "amplop digital" (nomor rekening/e-wallet untuk hadiah, opsional per event)
- [ ] Countdown menuju hari-H

## F. Fitur Pendukung Lintas Modul (Fase Lanjutan)

- [ ] Payment gateway otomatis (Midtrans/Xendit) — fase 2
- [ ] Katalog tema template siap pilih (untuk client yang tidak mau custom) — fase 2
- [ ] Sistem lisensi kalau nanti dijual sebagai template/source code ke developer lain — fase 3
- [ ] Multi-bahasa untuk undangan (opsional)
- [ ] Cetak instan di lokasi (integrasi printer) — fase lanjutan jika ada permintaan hardware on-site
