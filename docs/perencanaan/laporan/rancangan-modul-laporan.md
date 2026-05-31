# Rancangan Modul Laporan

Tanggal: 2026-05-14

## Tujuan

Dokumen ini menyimpan rancangan awal modul laporan untuk sistem POS. Fokus utamanya:

- memisahkan laporan operasional, manajerial, dan keuangan
- membatasi akses laporan berdasarkan role
- menyiapkan tahapan implementasi yang realistis terhadap struktur sistem saat ini

## Prinsip Dasar

1. Kasir tidak perlu akses ke laporan global atau data sensitif.
2. Admin fokus ke laporan operasional outlet.
3. Super admin atau owner mendapat akses penuh, termasuk laba rugi.
4. Laporan penjualan dapat dibuat lebih dulu tanpa menunggu integrasi resep ke HPP.
5. Laporan laba rugi yang akurat sebaiknya menunggu integrasi resep, konsumsi bahan, dan HPP.

## Klasifikasi Laporan

### 1. Laporan Operasional

Bisa dibuat dengan data yang sudah ada.

Cakupan:

- penjualan harian
- jumlah transaksi
- metode pembayaran
- diskon
- top menu berdasarkan qty dan omzet
- sesi kasir
- selisih kas
- absensi kasir
- stok menipis

Sumber data:

- orders
- order_items
- payments
- cashier_sessions
- attendances
- stock_levels
- menu_variants

Target role:

- admin
- super_admin
- supervisor jika nanti ada

### 2. Laporan Manajerial

Fokus ke monitoring performa dan tren.

Cakupan:

- tren penjualan harian, mingguan, bulanan
- produk terlaris
- produk kurang laku
- rata-rata nilai transaksi
- performa kasir
- performa outlet
- efektivitas diskon member

Target role:

- admin
- super_admin
- supervisor dengan scope outlet

### 3. Laporan Keuangan / Laba Rugi

Ini tahap lanjutan dan tidak ideal dibuat sebelum engine HPP siap.

Cakupan:

- HPP per item
- HPP per order
- laba kotor
- margin per menu
- laba per periode
- kontribusi produk

Ketergantungan:

- resep terhubung ke konsumsi bahan baku
- harga bahan baku dipakai untuk hitung HPP
- ada snapshot biaya saat transaksi terjadi

Target role:

- super_admin
- owner
- admin hanya jika memang diberi akses khusus

## Role dan Hak Akses

### Kasir

Boleh:

- melihat transaksi sendiri
- melihat shift sendiri
- melihat sesi kas sendiri

Tidak boleh:

- melihat laporan global
- melihat margin
- melihat laba rugi
- melihat laporan lintas kasir atau lintas outlet

### Admin

Boleh:

- melihat laporan penjualan
- melihat laporan kasir
- melihat laporan absensi
- melihat laporan stok
- melihat dashboard operasional

Perlu dipertimbangkan terpisah:

- akses ke laporan laba rugi

### Supervisor / Manager Outlet

Boleh:

- melihat laporan outlet yang dia tangani
- melihat sesi kas, absensi, penjualan, dan stok outlet

Tidak perlu:

- akses ke seluruh outlet jika bukan tanggung jawabnya

### Super Admin / Owner

Boleh:

- melihat semua laporan
- melihat laporan laba rugi
- melihat performa lintas outlet
- melihat data sensitif dan historis penuh

## Permission yang Disarankan

- ViewSalesReport:Report
- ViewCashierReport:Report
- ViewAttendanceReport:Report
- ViewStockReport:Report
- ViewManagementReport:Report
- ViewProfitLossReport:Report

Pembagian awal:

- kasir: tidak diberi permission laporan global
- admin: ViewSalesReport, ViewCashierReport, ViewAttendanceReport, ViewStockReport
- supervisor: sama dengan admin tetapi nanti dibatasi scope outlet
- super_admin: semua permission

## Struktur Modul

Disarankan memakai navigation group `Reports` dengan beberapa halaman terpisah:

- Laporan Penjualan
- Laporan Kasir
- Laporan Absensi
- Laporan Stok
- Laporan Laba Rugi

Jangan gabungkan semua ke satu halaman besar pada fase awal.

## Tahapan Implementasi

### Fase 1

- Laporan Penjualan
- Laporan Sesi Kasir
- Laporan Absensi
- Laporan Stok Menipis

### Fase 2

- Dashboard manajerial
- tren penjualan
- performa menu
- performa kasir

### Fase 3

- integrasi resep ke HPP
- konsumsi bahan otomatis saat transaksi
- snapshot biaya per transaksi

### Fase 4

- Laporan Laba Rugi
- margin per menu
- profitabilitas per periode

## Catatan Teknis Penting

1. Laporan penjualan tidak wajib menunggu resep.
2. Laporan laba rugi yang akurat sebaiknya menunggu HPP.
3. Jika laba rugi dipaksa dibuat tanpa HPP, hasilnya hanya estimasi kasar.
4. Scope data per role harus dibatasi di query, bukan hanya di menu navigasi.
5. Jika nanti ada multi outlet, semua laporan harus mendukung filter outlet.

## Rekomendasi

Urutan paling aman untuk implementasi:

1. bangun laporan operasional lebih dulu
2. siapkan permission dan scope role
3. tambah dashboard manajerial
4. integrasikan resep ke HPP
5. baru buat laporan laba rugi penuh

## Status

Dokumen ini adalah rancangan awal dan disimpan sebagai referensi sebelum implementasi modul laporan dimulai.
