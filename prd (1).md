# PRD.md — Product Requirements Document

## 1. Visi Produk

Membangun 1 platform terintegrasi yang menggabungkan 4 kebutuhan populer di industri event (wedding, birthday, corporate) — Photobooth Online, Guest Photographer, Voice Note Ucapan, dan Undangan Digital — supaya client cukup pakai 1 sistem dan 1 QR code untuk semua kebutuhan dokumentasi & interaksi tamu di acaranya.

## 2. Masalah yang Dipecahkan

- Client harus pesan banyak vendor terpisah (photobooth, dokumentasi tamu, digital guestbook, jasa undangan) untuk 1 acara — repot dikoordinasi dan lebih mahal totalnya.
- Momen personal dari sudut pandang tamu (foto candid, ucapan suara) sering hilang karena hanya mengandalkan fotografer profesional.
- Tamu harus buka banyak link/QR berbeda untuk tiap kebutuhan.

## 3. Target Pasar & Segmentasi

| Jenis Acara | Fitur Relevan | Range Harga Jual (referensi) |
|---|---|---|
| Wedding / Resepsi | Semua 4 modul | Rp1,5 juta – 4 juta |
| Engagement / Lamaran | Undangan + Photobooth | Rp800rb – 2 juta |
| Ulang Tahun | Undangan + Photobooth + VN | Rp600rb – 1,8 juta |
| Baby Shower / Aqiqah | Undangan + Photobooth + VN | Rp500rb – 1,3 juta |
| Corporate Event | Photobooth + Galeri live | Rp1 juta – 3 juta |
| Graduation / Wisuda | Photobooth + Guest Photographer | Rp700rb – 2 juta |
| Reuni / Komunitas | Photobooth + VN | Rp500rb – 1,5 juta |

Catatan: harga final ditentukan lewat pitching manual (WA), bukan checkout otomatis di sistem. Range di atas adalah acuan internal, bukan harga yang ditampilkan fixed ke publik (ditampilkan sebagai "mulai dari").

## 4. Model Bisnis

- **Bukan self-service SaaS murni.** Client tidak daftar & bayar sendiri otomatis di sistem.
- Alur: Client lihat landing page (harga "mulai dari") → klik tombol WA → nego & deal harga manual → **Admin (pemilik project) input manual** akun client + event lewat admin panel setelah pembayaran dikonfirmasi di luar sistem.
- Desain (tema undangan, frame photobooth) dibuat **custom per client** hasil pitching — bukan pilih dari katalog template. Desainer membuat asset, developer/admin input lewat admin panel (frame) atau deploy view baru (tema).
- Pendapatan tambahan: jasa desain custom (komponen harga terbesar) + perpanjangan masa penyimpanan storage (upsell).

## 5. Persona Pengguna

1. **Client** — pemilik acara (calon pengantin, keluarga ulang tahun, WO, HRD corporate). Butuh: dashboard monitoring hasil real-time, kemudahan sebar QR, hasil akhir yang bisa diunduh/diarsip.
2. **Tamu** — pengguna akhir modul publik (photobooth, guest photographer, VN, undangan+RSVP). Tidak perlu akun/login. Akses lewat scan QR atau link.
3. **Admin (Anda)** — pemilik sistem. Provisioning client, upload frame/tema, monitoring server, moderasi konten jika perlu.

## 6. Modul Produk (Ringkasan)

Lihat `features.md` untuk daftar fitur detail per modul.

- **A. Core** — auth, manajemen client, manajemen event, admin panel, QR generator, theme/frame engine.
- **B. Photobooth Online** — scan QR → kamera → ambil foto batas tertentu → pilih frame → compose → download.
- **C. Guest Photographer** — tamu motret pengantin/objek acara → masuk galeri live → client bisa moderasi & download arsip.
- **D. Voice Note Ucapan** — tamu rekam VN → masuk "kotak ucapan" → client dengarkan di dashboard.
- **E. Undangan Digital** — halaman undangan custom per event, RSVP, integrasi ke modul lain.

## 7. Batasan & Asumsi (Out of Scope untuk V1)

- Tidak ada payment gateway otomatis (Midtrans/Xendit) di V1 — fase 2 kalau volume client sudah besar.
- Tidak ada katalog tema template siap pilih — semua custom by request (bisa jadi fase 2: tambah opsi katalog untuk client yang tidak mau custom).
- Tidak ada aplikasi native mobile — semua berbasis web responsive.
- Tidak ada arsitektur multi-tenant terpisah database — cukup `event_id` sebagai pemisah data dalam 1 database.
- Retensi storage foto/audio dibatasi sesuai paket (mis. 1-3 bulan), setelah itu diarsipkan/dihapus kecuali client bayar perpanjangan.

## 8. Metrik Keberhasilan (Success Metrics)

- Jumlah event aktif per bulan.
- Rata-rata jumlah tamu yang menggunakan tiap modul per event.
- Waktu load halaman publik di kondisi jaringan venue (target < 3 detik di 4G).
- Zero downtime saat traffic spike acara berlangsung.
- Tingkat konversi dari "lihat landing page" ke "chat WA".

## 9. Risiko yang Diketahui

- Traffic spike bersamaan saat resepsi berlangsung (puluhan tamu akses dalam waktu singkat).
- Ketergantungan pada akses kamera/mic browser — wajib HTTPS, tidak semua tamu familiar mengizinkan akses.
- Biaya storage membengkak seiring bertambah banyak event aktif.
- Kualitas hasil sangat bergantung pada kecepatan tim desain untuk custom tema/frame per client (bottleneck non-teknis).
