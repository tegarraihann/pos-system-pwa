# Import Histori AHWA Warkop April 2026

Dokumen PDF `docs/detil_penjualan_2026_05_22_15_08_28.pdf` sekarang bisa diimpor ke tabel staging histori, bukan langsung ke transaksi final.

## Tujuan

- Menyimpan histori penjualan April 2026 ke sistem.
- Memetakan nama produk PDF ke master menu AHWA Warkop yang sudah disiapkan.
- Menandai transaksi yang sudah cocok penuh, masih ambigu, atau belum termapping.
- Menjaga laporan operasional dan laporan keuangan tetap aman, karena data PDF ini belum punya qty item yang eksplisit.

## Tabel Staging

1. `historical_order_imports`
   - menyimpan header transaksi
   - total transaksi
   - metode bayar raw dan hasil mapping
   - selisih terhadap harga master
   - status mapping

2. `historical_order_import_items`
   - menyimpan item hasil ekstraksi PDF
   - nama item raw
   - nama item yang dinormalisasi
   - mapping ke `menu_variant_id`
   - qty tertera
   - qty inferensi

## Status Mapping

- `matched`
  - semua item termapping
  - total transaksi cocok dengan harga master
- `partial`
  - sebagian item termapping, sebagian belum
- `ambiguous`
  - semua item termapping, tetapi total transaksi tidak sama dengan harga master
  - biasanya berarti qty aktual > 1 atau harga historis berbeda
- `unmatched`
  - seluruh item belum termapping

## Cara Menjalankan

```powershell
php artisan migrate
php artisan db:seed --class=AhwaWarkopMasterDataSeeder
php artisan historical:import-ahwa-sales
```

Kalau file PDF dipindah, jalankan dengan path custom:

```powershell
php artisan historical:import-ahwa-sales docs/detil_penjualan_2026_05_22_15_08_28.pdf
```

## Catatan Penting

- Import ini belum membuat `orders`, `order_items`, atau `payments` final.
- Import ini dipakai sebagai fondasi review dan migrasi lanjutan.
- Transaksi tunggal seperti `PRIMA 600 ML` dengan total `Rp10.000` akan diinferensikan sebagai qty `2`.
- Transaksi multi-item yang totalnya tidak cocok dengan harga master akan ditandai `ambiguous`, bukan dipaksakan masuk ke transaksi final.
