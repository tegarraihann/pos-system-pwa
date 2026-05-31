# Resep Awal AHWA Warkop

Seeder ini membuat resep awal untuk menu prioritas berdasarkan:

- master menu aktual hasil ekstraksi PDF penjualan April 2026
- master bahan baku awal yang disusun untuk menu racik dan menu olahan utama

## Tujuan

- mengaktifkan fondasi HPP pada menu yang paling penting lebih dulu
- membuat basis awal untuk laporan laba rugi dan costing
- mempermudah tahap review resep detail berikutnya

## Cakupan Resep Awal

### Minuman racik

- Bluberry Yoghurt
- Coffee Caramel
- Coffee Vanila
- Coklat
- Kopi Ahwa
- Kopi Gula Aren
- Kopi Susu
- Lemon Tea
- Matcha
- Taro
- Thai Tea

### Makanan dan snack olahan

- BAKSO
- KARI AYAM
- KENTANG GORENG
- UBI GORENG
- MIE BANGLADESH
- INDOMIE GORENG
- INDOMIE KUAH
- POP MIE SOTO AYAM
- NUGGET
- SOSIS GORENG
- OTAK-OTAK
- MIX PLATTER
- PEDES DOWER
- PEDES GLEDEK
- NANAS
- SEMANGKA

## Catatan

- resep ini adalah **fondasi awal**, bukan final operasional
- angka kuantitas masih berupa **estimasi awal yang realistis**
- menu botolan, snack kemasan, dan item non-prioritas belum dibuatkan resep di tahap ini

## Cara Menjalankan

```powershell
php artisan db:seed --class=AhwaWarkopInitialRecipesSeeder
```

Seeder ini otomatis memanggil:

- `AhwaWarkopMasterDataSeeder`
- `AhwaWarkopInitialIngredientsSeeder`

sehingga bisa dijalankan mandiri.
