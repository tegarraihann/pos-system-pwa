# POS System PWA

Aplikasi POS berbasis Laravel + Filament, lengkap dengan manajemen user/role, KDS, realtime (Reverb), dan integrasi pembayaran (opsional).

## Ringkasan Fitur
- POS kasir + antrian order
- Manajemen menu, varian, resep, bahan baku
- Manajemen user/role/permission (Spatie + Filament Shield)
- Kitchen Display System (KDS)
- Realtime (opsional)
- PWA (opsional)
- Midtrans (opsional)

---

## Syarat Singkat
Yang dibutuhkan sebelum mulai:
- PHP 8.2+
- Composer
- MySQL/MariaDB
- Git
- (Opsional) Node.js + npm untuk build asset

---

## Instalasi & Setup (Lengkap dari Nol)
Jalankan perintah berikut berurutan di folder project:

1) Install dependency
```bash
composer install
```

2) Buat file konfigurasi dasar
```bash
cp .env.example .env
php artisan key:generate
```

3) Isi koneksi database di `.env`
Contoh:
```env
DB_DATABASE=pos-system
DB_USERNAME=root
DB_PASSWORD=
```

4) Buat tabel database
```bash
php artisan migrate
```

5) Generate permission & policy (wajib)
```bash
php artisan shield:generate --all --panel=admin
```
Saat ditanya, pilih **Policies & Permissions**.

6) Isi role + super admin
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

7) (Opsional) Data demo
```bash
php artisan db:seed --class=PosDemoSeeder
```

8) Link storage untuk upload gambar
```bash
php artisan storage:link
```

9) Bersihkan cache
```bash
php artisan optimize:clear
```

10) Jalankan aplikasi
```bash
php artisan serve
```
Buka:
```
http://127.0.0.1:8000/admin
```

---

## Akun Awal (Default)
Login admin awal:
- Email: `superadmin@example.com`
- Password: `password`

> Jika menu **Users** tidak muncul, pastikan langkah 5 & 6 dijalankan, lalu login ulang.

---

## Mengatur Role (Admin / Kasir / Kitchen)
Masuk sebagai super admin ? menu **Roles** ? pilih role ? centang permission ? **Save changes**.

Pastikan user yang login sudah diberi role yang benar.

---

## Realtime (Opsional)
Jika ingin realtime untuk KDS dan order:
```bash
php artisan reverb:start
```
Pastikan `.env` sudah terisi `REVERB_*`.

---

## Pembayaran Midtrans (Opsional)
Isi `.env`:
```env
MIDTRANS_SERVER_KEY=...
MIDTRANS_CLIENT_KEY=...
MIDTRANS_IS_PRODUCTION=false
```
Jika pakai webhook:
```
https://domainkamu.com/midtrans/notification
```
> Untuk produksi, wajib HTTPS.

---

## PWA (Opsional)
Jika PWA aktif, saat dibuka dari browser:
- Akan muncul opsi �Install� (tergantung perangkat/browser)
- Jika tidak muncul, gunakan banner install yang tampil otomatis

---

## Jika Ada Kendala
Contoh kendala umum:
- Menu Users tidak muncul ? ulangi langkah 5�6, lalu login ulang.
- Data demo gagal ? pastikan migrasi lengkap.
- Upload gambar gagal ? jalankan `php artisan storage:link`.

---

## Catatan
Dokumentasi tambahan tersedia di folder `docs/`.
Jika butuh panduan khusus (PWA, Reverb, Midtrans), buka file terkait di sana.
