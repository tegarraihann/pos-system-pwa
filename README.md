# POS System

Aplikasi POS berbasis **Laravel 12** dan **Filament 4** untuk kebutuhan:
- POS kasir
- manajemen menu, varian, resep, bahan baku, dan stok
- absensi kasir dan sesi kasir
- QR public ordering
- laporan operasional dan laporan keuangan awal
- migrasi data aktual coffee shop

Dokumen ini fokus ke **cara setup project dari nol** dengan langkah yang sederhana dan aman untuk pemula.

## 1. Kebutuhan Sistem

Siapkan dulu:
- PHP **8.2** atau lebih baru
- Composer
- MySQL / MariaDB
- Node.js + npm
- Git

## 2. Clone Project

```powershell
git clone https://github.com/tegarraihann/pos-system-pwa.git
cd pos-system-pwa
```

Kalau project sudah ada di folder lokal, cukup buka folder project-nya.

## 3. Install Dependency

```powershell
composer install
npm install
```

## 4. Buat File `.env`

```powershell
Copy-Item .env.example .env
```

## 5. Atur Database di `.env`

Contoh paling umum:

```env
APP_NAME="POS System"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos-system
DB_USERNAME=root
DB_PASSWORD=
```

Pastikan database `pos-system` sudah dibuat di MySQL.

## 6. Generate Key Aplikasi

```powershell
php artisan key:generate
```

## 7. Jalankan Migrasi

```powershell
php artisan migrate
```

## 8. Seed Data Dasar Project

Langkah ini membuat:
- role dan permission dasar
- user login default
- lokasi stok default
- data demo POS

```powershell
php artisan db:seed
```

Seeder default menjalankan:
- `RolesAndPermissionsSeeder`
- `StockLocationsSeeder`
- `PosDemoSeeder`

## 9. Link Storage dan Bersihkan Cache

```powershell
php artisan storage:link
php artisan permission:cache-reset
php artisan optimize:clear
```

## 10. Jalankan Frontend dan Backend

Terminal 1:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Terminal 2:

```powershell
npm run dev
```

Buka aplikasi di:

```text
http://127.0.0.1:8000/admin
```

## 11. Akun Login Default

- Super Admin
  - Email: `superadmin@example.com`
  - Password: `password`
- Admin
  - Email: `admin@example.com`
  - Password: `password`

## 12. Setup Minimum Selesai

Kalau Anda hanya ingin menjalankan project dengan **data demo**, sampai langkah ini sudah cukup.

---

# Setup Data Aktual AHWA Warkop

Bagian ini hanya dijalankan jika Anda ingin mengaktifkan:
- master menu aktual dari data coffee shop
- bahan baku awal
- resep awal
- stok awal
- import histori penjualan April 2026 dari PDF

## 13. Seed Master Data Aktual

Jalankan berurutan:

```powershell
php artisan db:seed --class=AhwaWarkopMasterDataSeeder
php artisan db:seed --class=AhwaWarkopInitialIngredientsSeeder
php artisan db:seed --class=AhwaWarkopInitialRecipesSeeder
php artisan db:seed --class=AhwaWarkopInitialStockSeeder
```

Seeder tersebut akan membuat:
- outlet `AHWA Warkop`
- master menu aktual
- bahan baku awal
- resep awal menu prioritas
- stok awal bahan baku

## 14. Import Histori Penjualan PDF ke Staging

File sumber yang dipakai:

```text
docs/detil_penjualan_2026_05_22_15_08_28.pdf
```

Jalankan:

```powershell
php artisan historical:import-ahwa-sales
```

Hasilnya:
- histori masuk ke tabel staging
- belum langsung jadi `orders` final
- transaksi akan diberi status:
  - `matched`
  - `partial`
  - `ambiguous`
  - `unmatched`

## 15. Review Histori di Panel Admin

Masuk ke panel admin lalu buka menu:

```text
Data migration > Review Histori
```

Di halaman ini Anda bisa:
- melihat transaksi hasil import PDF
- memeriksa transaksi yang masih `partial`, `ambiguous`, atau `unmatched`
- membuka detail transaksi
- review item satu per satu
- memperbaiki master menu atau qty inferensi
- menandai transaksi sebagai **Siap Migrasi**

## 16. Migrasikan Histori yang Sudah Valid ke Order Final

Setelah transaksi staging sudah direview dan statusnya **Siap Migrasi**, jalankan:

```powershell
php artisan historical:migrate-ready-orders
```

Hasilnya:
- data masuk ke `orders`
- item masuk ke `order_items`
- pembayaran masuk ke `payments`
- snapshot HPP dan laba kotor ikut dihitung

Catatan penting:
- histori **tidak mengurangi stok aktif sekarang**
- jadi aman untuk laporan tanpa merusak stok bulan berjalan

---

# Integrasi Opsional

Bagian ini tidak wajib untuk setup dasar.

## Midtrans

Isi `.env`:

```env
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SANITIZE=true
MIDTRANS_3DS=true
```

Panduan tambahan:
- [docs/midtrans.md](docs/midtrans.md)

## Reverb / Realtime

Jalankan:

```powershell
php artisan reverb:start
```

Panduan tambahan:
- [docs/reverb-setup.md](docs/reverb-setup.md)

## PWA / Offline

Panduan tambahan:
- [docs/pwa-offline.md](docs/pwa-offline.md)

## QZ Tray

Jika memakai cetak struk QZ Tray, siapkan env:

```env
QZ_TRAY_CERTIFICATE=
QZ_TRAY_PRIVATE_KEY=
```

---

# Perintah Harian yang Paling Sering Dipakai

## Jalankan aplikasi lokal

```powershell
php artisan serve --host=127.0.0.1 --port=8000
npm run dev
```

## Kalau hanya ingin build frontend

```powershell
npm run build
```

## Bersihkan cache

```powershell
php artisan optimize:clear
php artisan permission:cache-reset
```

## Jalankan test

```powershell
php artisan test
```

---

# Troubleshooting

## MySQL tidak terkoneksi

Kalau muncul error seperti:

```text
SQLSTATE[HY000] [2002] No connection could be made...
```

cek:
- MySQL sudah menyala
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` di `.env` sudah benar

## Menu baru tidak muncul

Jalankan:

```powershell
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan permission:cache-reset
php artisan optimize:clear
```

lalu logout dan login lagi.

## Tampilan frontend terasa tidak update

Untuk development:

```powershell
npm run dev
```

Untuk build statis:

```powershell
npm run build
php artisan optimize:clear
```

## Import histori tidak masuk

Pastikan:
- file PDF ada di `docs/detil_penjualan_2026_05_22_15_08_28.pdf`
- master data AHWA sudah di-seed dulu

Perintah yang aman:

```powershell
php artisan db:seed --class=AhwaWarkopMasterDataSeeder
php artisan historical:import-ahwa-sales
```

---

# Dokumen Tambahan

- [docs/pos.md](docs/pos.md)
- [docs/midtrans.md](docs/midtrans.md)
- [docs/pwa-offline.md](docs/pwa-offline.md)
- [docs/reverb-setup.md](docs/reverb-setup.md)
- [docs/import_histori_ahwa_warkop_april_2026.md](docs/import_histori_ahwa_warkop_april_2026.md)
