# DESIGN.md — Panduan Desain & UX

## 1. Prinsip Utama

- **Mobile-first, bukan mobile-friendly.** Mayoritas tamu akses lewat HP saat scan QR di venue. Desain dan test dari layar kecil dulu, baru scale up ke desktop (untuk dashboard admin/client).
- **Ringan & cepat.** Venue sering punya koneksi wifi/4G tidak stabil (banyak orang connect bersamaan). Hindari asset berat, lazy-load gambar galeri, kompres semua asset visual.
- **Minim friksi.** Tamu tidak mau baca instruksi panjang — flow harus self-explanatory (misal: 1 tombol besar "Ambil Foto", bukan menu bertingkat).
- **Custom per client, bukan template generik.** Setiap tema undangan & frame dibuat sesuai request client hasil pitching — lihat bagian Theme Engine di bawah.

## 2. Dua Kategori Tampilan yang Berbeda

| Kategori | Karakteristik | Siapa yang Pakai |
|---|---|---|
| **Public-facing (tamu)** | Custom per event, sangat visual, branded sesuai request client | Tamu acara |
| **Internal (admin/client dashboard)** | Konsisten, fungsional, tidak perlu didesain ulang tiap client | Anda (admin) & client pemilik acara |

Dashboard admin/client **TIDAK ikut di-custom per event** — cukup 1 desain dashboard yang solid dan dipakai semua client, supaya efisien development. Yang di-custom hanya halaman publik (undangan, photobooth, dsb).

## 3. Theme Engine (Undangan Digital)

- Tiap tema = 1 folder view Blade + CSS terpisah, diidentifikasi lewat `theme_slug`.
- Proses kerja: Client pitching → deal desain → designer bikin mockup (Figma/gambar) → developer convert jadi Blade+CSS → upload ke server → assign `theme_slug` ke event lewat admin panel.
- Data yang direnderkan (nama mempelai, tanggal, galeri, RSVP form) selalu generik dari database — **hanya HTML/CSS yang berbeda per tema**, logic backend sama untuk semua tema.
- Sediakan 1 tema `default` sebagai fallback kalau `theme_slug` event belum di-set atau belum selesai dikerjakan designer.

## 4. Frame Engine (Photobooth)

- Frame = file PNG dengan area transparan sebagai slot foto tamu.
- Admin upload lewat form khusus di admin panel: upload file + tentukan koordinat tiap slot (x, y, width, height) — idealnya dengan tool visual drag-and-drop di atas preview frame, tersimpan sebagai JSON.
- Tidak butuh developer terlibat sama sekali setelah fitur upload frame ini jadi — murni data, bukan kode.
- Rekomendasi ukuran: standar rasio umum untuk cetak/share (misal 4:6 untuk photostrip, 1:1 untuk single frame Instagram-friendly).

## 5. Design System untuk Dashboard (Internal)

- Gunakan 1 set warna, tipografi, dan komponen konsisten (rekomendasi: Tailwind CSS, karena mudah dipadukan dengan Livewire).
- Komponen reusable: card statistik event, tabel data (client, event, RSVP), audio player inline untuk VN, image gallery grid untuk foto tamu.
- Dark mode opsional, tidak prioritas di V1.

## 6. Aksesibilitas Dasar

- Tombol besar dan jelas untuk aksi utama (ambil foto, rekam VN) — target minimal 44x44px untuk touch target di mobile.
- Kontras warna teks-background wajib cukup (terutama untuk tema custom — beri guideline ke designer, jangan sampai estetika mengorbankan keterbacaan).
- Pesan error/izin kamera-mic yang jelas dalam Bahasa Indonesia (misal: "Izinkan akses kamera untuk melanjutkan" bukan pesan error teknis browser).

## 7. Responsive Breakpoints (Acuan Tailwind Default)

- Mobile: < 640px (prioritas utama untuk semua halaman publik)
- Tablet: 640px – 1024px
- Desktop: > 1024px (prioritas untuk dashboard admin/client)
