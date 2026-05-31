# Stok Awal AHWA Warkop

Seeder ini membuat stok awal bahan baku untuk outlet `AHWA Warkop` sebagai mutasi masuk pembuka.

## Prinsip

- stok awal dibuat sebagai `StockMovement` bertipe `in`
- data tidak ditulis langsung ke `stock_levels`
- pendekatan ini menjaga histori pergerakan stok tetap konsisten

## Referensi Mutasi

- `reference_no`: `OPENING-AHWA-2026-05`

## Cakupan

- seluruh bahan baku awal dari `AhwaWarkopInitialIngredientsSeeder`
- kuantitas pembuka dibuat sebagai baseline operasional, bukan hasil stock opname final

## Cara Menjalankan

```powershell
php artisan db:seed --class=AhwaWarkopInitialStockSeeder
```

Seeder ini otomatis memanggil:

- `AhwaWarkopMasterDataSeeder`
- `AhwaWarkopInitialIngredientsSeeder`

## Catatan

- stok awal ini adalah baseline kerja awal
- jika nanti ada stock opname nyata dari outlet, nilai ini sebaiknya direvisi melalui mutasi penyesuaian
