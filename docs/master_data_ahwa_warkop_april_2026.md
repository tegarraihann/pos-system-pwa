# Master Data AHWA Warkop April 2026

Dokumen ini merangkum master data awal yang diekstrak dari `docs/detil_penjualan_2026_05_22_15_08_28.pdf`.

## Tujuan

- membangun master menu awal berdasarkan data aktual yang benar-benar terjual
- menyiapkan fondasi migrasi histori transaksi berikutnya
- meminimalkan input manual ulang pada tahap awal

## Ringkasan

- sumber data: laporan detail penjualan April 2026
- outlet terdeteksi: `AHWA Warkop`
- transaksi terbaca: `512`
- produk unik setelah normalisasi nama: `60`

## Aturan Harga

- `direct`: harga diambil dari transaksi tunggal dengan produk tunggal, lalu dipakai harga minimum yang konsisten
- `inferred`: harga disimpulkan dari kombinasi transaksi sederhana yang cukup konsisten
- `estimated`: harga masih estimasi awal, sehingga item dibuat nonaktif agar tidak langsung dipakai tanpa review

## Item Perlu Review Manual

Item berikut tetap dibuat sebagai master data, tetapi diset `nonaktif` karena harga belum cukup kuat dari PDF:

- `Nasi Goreng Ahwa`
- `STRAWBERRY CONE`
- `SWEET CORN`
- `Teh Tanjak`

## Cara Menjalankan Seeder

```powershell
php artisan db:seed --class=AhwaWarkopMasterDataSeeder
```

Seeder ini tidak dimasukkan ke `DatabaseSeeder` default agar tidak bercampur dengan demo data.

## Catatan

- seeder hanya membuat `master menu`, `varian default`, dan `outlet AHWA Warkop`
- seeder ini belum membuat `resep`, `stok awal`, `bahan baku`, atau `histori transaksi`
- untuk migrasi histori transaksi, sumber yang lebih ideal tetap `Excel/CSV` atau export database lama
