# Fitur Membership

Dokumen ini menjelaskan cara kerja fitur membership pada sistem POS.

## Tujuan Fitur

Fitur membership dipakai untuk memberikan keuntungan khusus kepada customer tertentu dalam bentuk potongan harga otomatis saat transaksi.

Dengan fitur ini:

- Customer biasa tetap bisa belanja seperti biasa.
- Customer member bisa mendapatkan diskon otomatis.
- Kasir tidak perlu menghitung diskon member secara manual.
- Riwayat transaksi tetap menyimpan besar diskon yang dipakai saat order dibuat.

## Cara Kerja Membership

Saat sebuah customer ditandai sebagai member, customer tersebut bisa diberi nilai diskon dalam bentuk persentase.

Contoh:

- Customer A adalah member dengan diskon `5%`
- Customer B adalah member dengan diskon `10%`
- Customer non-member tidak mendapat diskon member

Ketika kasir memilih customer member di POS:

1. Sistem membaca status member customer.
2. Sistem mengambil persentase diskon milik customer.
3. Sistem menghitung potongan dari subtotal belanja.
4. Sistem menampilkan potongan tersebut di ringkasan transaksi.
5. Total akhir otomatis berkurang sesuai diskon member.

## Data Membership di Customer

Pada data customer, terdapat dua informasi penting:

- `Member`
  - Menentukan apakah customer termasuk member atau bukan.
- `Diskon Member (%)`
  - Menentukan berapa persen potongan harga yang diberikan saat customer tersebut dipilih pada transaksi.

Jika customer bukan member:

- Diskon member dianggap `0%`
- Tidak ada potongan otomatis

## Alur di POS Kasir

Di modal pembayaran POS Kasir:

1. Kasir memilih customer.
2. Jika customer adalah member, sistem akan menampilkan informasi diskon aktif.
3. Kasir memilih metode pembayaran:
   - Tunai
   - Midtrans
4. Sistem menghitung total akhir berdasarkan diskon member.

Ringkasan transaksi akan menampilkan:

- Subtotal
- Diskon Member
- Pajak
- Total

## Rumus Perhitungan

Perhitungan membership dilakukan dari subtotal transaksi.

Rumus dasar:

- `Diskon Member = Subtotal x Persentase Diskon`
- `Total Akhir = Subtotal - Diskon Member`

Contoh:

- Subtotal: `Rp 100.000`
- Diskon Member: `5%`
- Potongan: `Rp 5.000`
- Total akhir: `Rp 95.000`

## Tersimpan di Order

Saat transaksi disimpan, sistem juga menyimpan data membership ke order:

- Persentase diskon member yang dipakai saat itu
- Nominal diskon member saat itu

Hal ini penting supaya:

- Jika data customer diubah di kemudian hari, transaksi lama tidak ikut berubah.
- Admin tetap bisa melihat riwayat diskon member sesuai kondisi saat transaksi terjadi.

## Tampil di Struk

Pada preview struk dan cetak struk, sistem menampilkan informasi berikut:

- Nama customer
- Tipe customer
- Diskon member
- Total diskon
- Total akhir

Dengan begitu:

- Kasir tahu diskon member benar-benar terpakai
- Customer bisa melihat manfaat membership secara jelas

## Tipe Customer

Secara umum ada dua kondisi:

- `Walk In`
  - Customer umum atau tanpa benefit member
- `Member`
  - Customer dengan potongan harga membership

Jika customer dipilih tetapi bukan member, transaksi tetap bisa berjalan, namun diskon member tetap `0`.

## Dampak ke Pembayaran

Fitur membership berlaku untuk:

- Pembayaran tunai
- Pembayaran Midtrans
- Sinkronisasi order offline

Artinya:

- Nominal yang dibayar customer selalu mengikuti total akhir setelah diskon member.
- Jika transaksi disinkronkan dari mode offline, data diskon member tetap ikut tersimpan.

## Catatan Penggunaan

- Diskon member hanya berlaku jika customer dipilih saat transaksi.
- Jika kasir tidak memilih customer, sistem menganggap transaksi sebagai `Walk In`.
- Jika customer yang dipilih bukan member, tidak ada diskon otomatis.
- Besaran diskon member diatur dari data customer, bukan dari POS.

## Rekomendasi Operasional

Supaya fitur ini konsisten dipakai:

- Pastikan customer member diberi persentase diskon yang benar.
- Biasakan kasir memilih customer sebelum konfirmasi pembayaran.
- Gunakan diskon membership sebagai benefit tetap, bukan sebagai diskon manual harian.

Dengan pola ini, membership akan lebih mudah dikelola dan hasil transaksi lebih konsisten.
