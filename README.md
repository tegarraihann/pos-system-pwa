# POS System PWA

Aplikasi POS berbasis Laravel 12 + Filament 4.
Fitur utama: POS kasir, KDS, manajemen stok, role/permission, realtime (Reverb), PWA, dan Midtrans (opsional).

## Fitur Utama
- POS Kasir
- Kitchen Display
- Manajemen menu, varian, resep, bahan baku
- Manajemen user, role, permission (Spatie + Shield)
- Absensi kasir
- Realtime (opsional)
- Midtrans (opsional)
- PWA (opsional)
- Cetak struk QZ Tray (opsional)

## Kebutuhan Sistem
- PHP 8.2 atau lebih baru
- Composer
- MySQL/MariaDB
- Node.js + npm
- Git

## Instalasi dari Nol (Urutan Aman)
Jalankan dari terminal di folder project.

### 1) Clone project
```bash
git clone https://github.com/tegarraihann/pos-system-pwa.git
cd pos-system-pwa
```

### 2) Install dependency backend dan frontend
```bash
composer install
npm install
```

### 3) Buat file `.env`
PowerShell (Windows):
```powershell
Copy-Item .env.example .env
```

Bash:
```bash
cp .env.example .env
```

### 4) Generate app key
```bash
php artisan key:generate
```

### 5) Atur koneksi database di `.env`
Contoh:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos-system
DB_USERNAME=root
DB_PASSWORD=
```

Pastikan database `pos-system` sudah dibuat di MySQL.

### 6) Jalankan migrasi
```bash
php artisan migrate
```

### 7) Generate policy + permission Shield (wajib)
```bash
php artisan shield:generate --all --panel=admin
```
Saat ada pertanyaan:
- `Would you like to select what to generate?` -> `yes`
- `What do you want to generate?` -> `Policies & Permissions`

### 8) Seed data role, user, dan data awal
```bash
php artisan db:seed
```

Seeder default akan menjalankan:
- `RolesAndPermissionsSeeder`
- `StockLocationsSeeder`
- `PosDemoSeeder`

### 9) Reset cache permission dan cache aplikasi
```bash
php artisan permission:cache-reset
php artisan optimize:clear
```

### 10) Link storage (wajib untuk upload gambar)
```bash
php artisan storage:link
```

### 11) Jalankan aplikasi
Terminal 1:
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Terminal 2:
```bash
npm run dev
```

Lalu buka:
```text
http://127.0.0.1:8000/admin
```

## Akun Login Default
- Super Admin
  - Email: `superadmin@example.com`
  - Password: `password`
- Admin
  - Email: `admin@example.com`
  - Password: `password`

## Setup User Baru
Jika ingin buat user baru:
```bash
php artisan make:filament-user
```

Setelah user dibuat, masuk sebagai super admin lalu:
- Buka menu `Users`
- Set role user (misalnya `kasir`, `kitchen`, `admin`)

## Menjalankan Realtime (Opsional)
```bash
php artisan reverb:start
```

Jika ingin akses publik via domain + cloudflared tunnel, ikuti:
- `docs/reverb-setup.md`

## Menjalankan Midtrans (Opsional)
Isi `.env`:
```env
MIDTRANS_SERVER_KEY=...
MIDTRANS_CLIENT_KEY=...
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SANITIZE=true
MIDTRANS_3DS=true
```

Lalu set webhook Midtrans ke:
```text
https://domain-anda/midtrans/notification
```

Panduan detail:
- `docs/midtrans.md`

## Menjalankan PWA dan Offline Cash (Opsional)
Panduan:
- `docs/pwa-offline.md`

## Cetak Struk QZ Tray (Opsional)
Jika memakai QZ Tray, siapkan:
- file certificate
- private key
- env untuk QZ

Lalu jalankan build/dev frontend seperti biasa.

## Quick Command Checklist
Jika ingin ringkas, ini urutan command inti:
```bash
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan shield:generate --all --panel=admin
php artisan db:seed
php artisan permission:cache-reset
php artisan storage:link
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000
```

## Troubleshooting

### Menu `Users` tidak muncul
Jalankan ulang:
```bash
php artisan shield:generate --all --panel=admin
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan permission:cache-reset
php artisan optimize:clear
```
Lalu logout dan login lagi.

### Reverb gagal start (port dipakai)
Ganti port Reverb di `.env`, contoh:
```env
REVERB_SERVER_PORT=8081
```

### `Invalid request (Unsupported SSL request)` saat `php artisan serve`
Biasanya terjadi karena URL HTTPS diarahkan ke server HTTP lokal.
Gunakan URL `http://127.0.0.1:8000` untuk akses lokal biasa.

### Midtrans sudah paid tapi status order belum berubah
- Pastikan webhook URL benar dan aktif
- Cek endpoint `POST /midtrans/notification`
- Cek log: `storage/logs/laravel.log`

## Dokumentasi Tambahan
- `docs/filament-setup.md`
- `docs/pos.md`
- `docs/midtrans.md`
- `docs/reverb-setup.md`
- `docs/pwa-offline.md`
