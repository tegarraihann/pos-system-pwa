# Master Bahan Baku Awal AHWA Warkop

Seeder ini disusun untuk menyiapkan master bahan baku awal berdasarkan menu aktual yang paling penting dari laporan penjualan April 2026.

## Cakupan Awal

Fokus tahap ini adalah menu yang paling relevan untuk fondasi HPP:

- minuman racik: `Matcha`, `Kopi Gula Aren`, `Kopi Susu`, `Kopi Ahwa`, `Coffee Caramel`, `Coffee Vanila`, `Coklat`, `Taro`, `Thai Tea`, `Bluberry Yoghurt`
- makanan dan snack olahan: `KARI AYAM`, `BAKSO`, `KENTANG GORENG`, `UBI GORENG`, `MIE BANGLADESH`, `INDOMIE GORENG`, `INDOMIE KUAH`, `POP MIE SOTO AYAM`, `NUGGET`, `SOSIS GORENG`, `OTAK-OTAK`, `MIX PLATTER`, `PEDES DOWER`, `PEDES GLEDEK`

## Yang Dibuat Seeder

- kategori bahan baku
- supplier awal
- master bahan baku inti

## Yang Belum Dibuat

- resep per menu
- stok awal
- mutasi stok awal
- HPP historis per transaksi lama

## Cara Menjalankan

```powershell
php artisan db:seed --class=AhwaWarkopInitialIngredientsSeeder
```

Seeder ini dibuat terpisah agar tidak bercampur dengan data demo.
