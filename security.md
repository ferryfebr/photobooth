# SECURITY.md — Panduan Keamanan

## 1. HTTPS Wajib di Semua Environment (Kecuali Local)

Browser modern **menolak akses kamera/mikrofon** (`getUserMedia`, `MediaRecorder`) di halaman non-HTTPS, kecuali `localhost`. Wajib SSL aktif di staging maupun production — gunakan wildcard SSL (`*.namasistem.com`) via Let's Encrypt (DNS-01 challenge) atau Cloudflare, karena subdomain per event bersifat dinamis.

## 2. Akses Endpoint Publik (Tamu) Tanpa Login

Tamu tidak login, jadi otorisasi tidak bisa pakai session/Sanctum biasa. Pendekatan yang wajib dipakai:

- Setiap event punya **slug unik** (bagian dari subdomain) yang sulit ditebak (kombinasi nama + random string, bukan hanya angka urut/ID auto-increment).
- Untuk aksi sensitif (submit foto, submit VN, submit RSVP), gunakan **Signed URL** Laravel (`URL::temporarySignedRoute`) yang di-encode dalam QR code, supaya link tidak bisa dipakai untuk event lain meski di-tebak.
- Jangan pernah expose `event_id` (integer auto-increment) langsung di URL publik — gunakan UUID atau slug.

## 3. Rate Limiting

Wajib diterapkan per modul untuk cegah abuse/spam storage:

| Endpoint | Batas yang Disarankan |
|---|---|
| Upload foto photobooth | Maks sesuai batas foto per sesi (mis. 4), lalu sesi ditutup |
| Upload foto guest photographer | Maks N foto per device/session (mis. 10), rate limit per menit untuk cegah spam script |
| Upload voice note | Maks durasi per rekaman + limit jumlah submit per device (mis. maks 3 VN per device) |
| Submit RSVP | 1 submit per device/session (cegah duplikat) |

Gunakan kombinasi rate limiter Laravel (`throttle` middleware) + fingerprint sisi client (session cookie/local storage id) — catatan: fingerprint browser bukan proteksi absolut, cukup untuk mencegah abuse kasual bukan serangan terencana.

## 4. Validasi File Upload

- Validasi **MIME type asli** file (bukan hanya ekstensi) untuk semua upload foto/audio.
- Batasi ukuran file maksimal per upload (foto misal maks 10MB sebelum compress, audio maks sesuai batas durasi).
- Simpan file dengan nama yang di-generate ulang (UUID), jangan pakai nama asli dari client untuk mencegah path traversal/collision.
- Scan tambahan (opsional, fase lanjutan): validasi dimensi gambar wajar untuk cegah file yang dimanipulasi jadi "gambar" padahal berisi payload lain.

## 5. Autentikasi & Otorisasi Internal

- Admin panel wajib pakai autentikasi terpisah dari akun client (`AdminUser` model berbeda dari `Client`).
- Rekomendasi: aktifkan **2FA** untuk akun admin (Anda), karena admin panel punya akses penuh ke semua data client.
- Kalau client diberi login dashboard, terapkan `EventPolicy` — client A tidak boleh bisa akses data event milik client B meski tahu URL/ID-nya (uji ini secara eksplisit di test).

## 6. Privasi Data Tamu

- Data yang dikumpulkan dari tamu (foto wajah, suara, nama di VN) adalah data pribadi — perlakukan dengan hati-hati:
  - Jangan expose galeri foto secara publik tanpa kontrol akses (galeri live hanya bisa diakses lewat link/kredensial yang diberikan ke client, bukan terindeks search engine — tambahkan `robots.txt`/`noindex` di semua halaman event).
  - Beri retention policy yang jelas (data dihapus otomatis setelah masa penyimpanan sesuai paket habis, lihat `PurgeExpiredEventDataJob` di `mvc.md`).
  - Cantumkan notifikasi singkat di halaman publik bahwa foto/audio akan dipakai dalam konteks acara ini (transparansi dasar, bukan syarat hukum formal — sesuaikan dengan kebutuhan bila nanti berkembang ke skala lebih besar).

## 7. Secrets & Environment

- `.env` tidak boleh pernah masuk ke git — pastikan `.gitignore` mencakupnya sejak commit pertama.
- Kredensial S3-compatible storage, database, dan API key pihak ketiga (WA API, dsb) hanya disimpan di `.env` server, tidak pernah hardcode di kode.
- Gunakan API key/token terpisah untuk environment local, staging, dan production.

## 8. Backup

- Backup database rutin (harian) — terutama karena tidak menggunakan managed VPS dengan backup otomatis bawaan (VPS Lite bersifat unmanaged).
- Backup konfigurasi frame/tema (bukan hanya file media) karena ini adalah aset bisnis yang sulit dibuat ulang.

## 9. Monitoring Dasar

- Uptime monitor (misal UptimeRobot) aktif sejak hari pertama sistem dipakai client sungguhan — penting untuk deteksi dini saat traffic spike di hari-H.
- Log error terpusat (Laravel log atau layanan seperti Sentry, jika budget memungkinkan di fase lanjutan) untuk deteksi kegagalan job (convert audio gagal, compose foto gagal, dsb) sebelum client komplain.
