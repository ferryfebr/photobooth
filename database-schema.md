# DATABASE-SCHEMA.md — Skema Database

Database tunggal (bukan multi-tenant terpisah). Pemisah data antar client adalah `event_id`/`client_id` di setiap tabel modul.

## 1. `admin_users`
Akun untuk Anda (pemilik sistem), terpisah dari client.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| name | varchar | |
| email | varchar, unique | |
| password | varchar | hashed |
| two_factor_secret | text, nullable | untuk 2FA |
| created_at, updated_at | timestamp | |

## 2. `clients`
Data client, diinput manual oleh admin setelah deal via WA.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| name | varchar | |
| phone | varchar | nomor WA client |
| email | varchar, nullable | |
| notes | text, nullable | catatan internal (hasil nego, dsb) |
| status | enum | `active`, `inactive` |
| created_at, updated_at | timestamp | |

## 3. `packages`
Referensi paket (bukan harga fixed — hanya menentukan modul apa saja aktif secara default).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| name | varchar | mis. "Wedding", "Birthday", "Corporate" |
| default_modules | json | daftar modul default yang aktif |
| default_storage_days | integer | masa penyimpanan default (mis. 90 hari) |
| created_at, updated_at | timestamp | |

## 4. `events`
Tabel inti — 1 baris = 1 acara.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| client_id | bigint, FK → clients | |
| package_id | bigint, FK → packages, nullable | |
| name | varchar | nama acara, mis. "Pernikahan Siti & Budi" |
| slug | varchar, unique | dipakai untuk subdomain, mis. `siti-budi-a8x2` |
| theme_slug | varchar, nullable | referensi ke folder view tema undangan |
| event_date | date | tanggal acara |
| photobooth_active_from, photobooth_active_until | datetime, nullable | |
| storage_expires_at | datetime | kapan data event ini akan di-purge |
| status | enum | `draft`, `active`, `completed`, `archived` |
| created_at, updated_at | timestamp | |

## 5. `event_modules`
Pivot: modul mana saja yang aktif untuk event ini (bisa beda dari default paket kalau ada kustomisasi).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| event_id | bigint, FK → events | |
| module_key | enum | `photobooth`, `guest_photographer`, `voice_note`, `invitation` |
| is_active | boolean | |

## 6. `frames`
Frame photobooth, di-upload lewat admin panel.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| event_id | bigint, FK → events | frame biasanya spesifik per event (custom) |
| name | varchar | |
| file_path | varchar | path ke S3-compatible storage |
| slot_coordinates | json | array `[{x, y, width, height}, ...]` per slot foto |
| photo_count | integer | jumlah foto yang dibutuhkan frame ini |
| created_at, updated_at | timestamp | |

## 7. `photobooth_sessions`
1 sesi = 1 kali tamu pakai photobooth.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| event_id | bigint, FK → events | |
| frame_id | bigint, FK → frames, nullable | |
| device_fingerprint | varchar | untuk rate limiting |
| status | enum | `capturing`, `composed`, `downloaded` |
| result_file_path | varchar, nullable | hasil akhir setelah compose |
| created_at, updated_at | timestamp | |

## 8. `photobooth_photos`
Foto mentah tiap jepretan dalam 1 sesi.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| photobooth_session_id | bigint, FK | |
| file_path | varchar | |
| order | integer | urutan foto dalam sesi |
| created_at | timestamp | |

## 9. `guest_photos`
Foto dari modul Guest Photographer.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| event_id | bigint, FK → events | |
| file_path | varchar | |
| device_fingerprint | varchar | untuk rate limiting per tamu |
| is_approved | boolean, default true | untuk moderasi (hide oleh client/admin) |
| created_at | timestamp | |

## 10. `voice_notes`
Rekaman ucapan.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| event_id | bigint, FK → events | |
| sender_name | varchar, nullable | opsional, bisa anonim |
| file_path | varchar | path file mp3 hasil convert |
| duration_seconds | integer | |
| device_fingerprint | varchar | untuk rate limiting |
| created_at | timestamp | |

## 11. `invitation_themes` (Registry, Bukan Isi Tema)
Sekadar daftar tema yang tersedia di sistem — isi tema sesungguhnya ada di file view (`resources/views/invitations/{slug}`), tabel ini hanya metadata.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| slug | varchar, unique | cocok dengan nama folder view |
| label | varchar | nama untuk ditampilkan di dropdown admin, mis. "Wedding Siti & Budi (Custom)" |
| created_for_event_id | bigint, FK → events, nullable | tema ini dibuat khusus untuk event siapa |
| created_at, updated_at | timestamp | |

## 12. `rsvps`
Jawaban RSVP dari tamu.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| event_id | bigint, FK → events | |
| guest_name | varchar | |
| attendance | enum | `hadir`, `tidak_hadir` |
| guest_count | integer, default 1 | jumlah tamu yang datang |
| note | text, nullable | |
| created_at | timestamp | |

## 13. `activity_logs` (Analitik Ringan)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| event_id | bigint, FK → events | |
| module_key | varchar | modul mana yang diakses |
| action | varchar | mis. `photo_captured`, `vn_recorded`, `rsvp_submitted` |
| created_at | timestamp | |

## Relasi Ringkas (ERD Sederhana)

```
clients ──< events >── packages
events ──< event_modules
events ──< frames ──< photobooth_sessions ──< photobooth_photos
events ──< guest_photos
events ──< voice_notes
events ──< rsvps
events ──< activity_logs
events 1─1 invitation_themes (via slug, bukan FK langsung)
```

## Catatan Migrasi

- Semua tabel modul (`frames`, `photobooth_*`, `guest_photos`, `voice_notes`, `rsvps`) wajib punya `event_id` dengan `onDelete('cascade')` supaya konsisten saat event dihapus/di-purge.
- `file_path` di semua tabel media menyimpan path relatif ke disk S3-compatible, bukan URL penuh — generate URL signed saat dibutuhkan (terutama kalau storage bersifat private, bukan public bucket).
- Index tambahan yang disarankan: `events.slug` (unique, dipakai untuk lookup subdomain di setiap request), `*.event_id` (foreign key index) di semua tabel modul.
