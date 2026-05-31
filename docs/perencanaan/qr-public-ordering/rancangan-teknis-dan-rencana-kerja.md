# Rancangan Teknis dan Rencana Kerja

## Perbaikan Fitur QR Public Ordering

Dokumen ini merangkum rancangan teknis dan rencana kerja untuk memperbaiki fitur QR public ordering agar customer dapat memesan menu, lalu langsung melanjutkan ke pembayaran Midtrans tanpa menunggu konfirmasi manual dari kasir.

## Latar Belakang

Implementasi QR public ordering saat ini masih memakai alur:

- Customer scan QR code
- Customer pilih menu
- Customer isi form guest
- Customer klik kirim pesanan
- Order masuk dengan status `pending_confirmation`
- Kasir atau admin mengubah status menjadi `draft`

Masalah dari alur tersebut:

- Belum ada hubungan langsung antara order publik dan pembayaran Midtrans
- Status order belum jelas untuk membedakan:
  - belum bayar
  - sudah bayar
  - sedang diproses
  - selesai disajikan
- Order publik yang sudah dikonfirmasi belum punya jalur operasional yang rapi sampai selesai
- Status `served` berpotensi tercampur antara arti "sudah dibayar" dan "pesanan sudah selesai"

Karena itu, fitur ini perlu diubah ke model `pay-first`, yaitu customer memesan lalu langsung diarahkan ke pembayaran Midtrans.

## Tujuan Perbaikan

- Customer bisa scan QR, pilih menu, isi data singkat, lalu langsung lanjut ke pembayaran
- Sistem memakai konfigurasi Midtrans yang sudah ada di project
- Hanya order yang pembayarannya valid yang masuk ke antrean operasional
- Status order dan status payment dipisahkan dengan jelas
- Flow public ordering tidak merusak alur POS internal yang sudah berjalan

## Rancangan Flow Baru

### Flow customer

1. Customer scan QR code meja
2. Customer membuka halaman katalog menu publik
3. Customer memilih menu dan jumlah
4. Customer mengisi nama, nomor HP opsional, dan catatan
5. Customer klik tombol `Lanjut ke Pembayaran`
6. Sistem membuat order publik dan payment pending
7. Sistem memunculkan pembayaran Midtrans Snap atau QRIS
8. Customer menyelesaikan pembayaran
9. Webhook Midtrans memperbarui status payment dan order
10. Jika pembayaran sukses, order masuk ke antrean outlet

### Flow internal

1. Order dengan status `pending_payment` belum boleh diproses outlet
2. Jika payment sukses, order menjadi `paid`
3. Staff outlet mengubah order ke `processing` saat mulai menyiapkan
4. Staff outlet mengubah order ke `served` saat pesanan selesai dan diserahkan
5. Jika pembayaran expired atau gagal, order tidak diproses

## Rancangan Status

### Status order yang disarankan

- `pending_payment`
  - order sudah dibuat, menunggu pembayaran customer
- `paid`
  - pembayaran sukses, order sah masuk operasional
- `processing`
  - staff outlet sedang memproses pesanan
- `served`
  - pesanan selesai dan sudah diserahkan
- `canceled`
  - dibatalkan manual
- `expired`
  - pembayaran kadaluarsa
- `failed`
  - opsional, dipakai bila ingin membedakan kegagalan pembayaran

### Status payment yang dipertahankan

- `pending`
- `paid`
- `failed`
- `expired`
- `canceled`
- `refunded`

## Aturan Utama

- `served` tidak boleh lagi dipakai sebagai arti "sudah dibayar"
- Payment sukses hanya mengubah order ke `paid`
- Order baru boleh diproses outlet jika payment berstatus `paid`
- Webhook Midtrans menjadi source of truth untuk status pembayaran
- Flow POS internal tetap memakai alurnya sendiri dan tidak boleh rusak karena perubahan QR ordering

## Perubahan Teknis yang Diperlukan

### 1. Model Order

File terdampak:

- `app/Models/Order.php`

Perubahan:

- Tambah konstanta status baru:
  - `pending_payment`
  - `paid`
  - `processing`
  - `expired`
  - opsional `failed`
- Perbarui `statusOptions()`
- Pertahankan `order_source = public_qr`
- Jangan memaksa semua order internal memakai status baru secara agresif

Catatan:

- Flow POS internal yang sekarang memakai `draft -> served` harus tetap aman
- Logic untuk order `public_qr` perlu dibedakan dari `pos`

### 2. Service Public Ordering

File terdampak:

- `app/Services/PublicOrderingService.php`

Perubahan:

- Ganti flow `placeOrder()` menjadi dua tahap:
  - buat order status `pending_payment`
  - buat payment status `pending`
- Simpan `payment_method = midtrans`
- Siapkan `gateway_ref` untuk Midtrans
- Pastikan validasi stok dan menu tetap dijalankan sebelum transaksi dibuat

Rekomendasi:

- Pisahkan method untuk:
  - create order pending payment
  - create payment pending
  - start Midtrans transaction

### 3. Controller Public Ordering

File terdampak:

- `app/Http/Controllers/PublicOrderingController.php`

Perubahan:

- Tombol submit tidak lagi sekadar membuat order dan redirect dengan flash message
- Setelah submit:
  - buat order `pending_payment`
  - buat payment `pending`
  - panggil Midtrans Snap
  - arahkan customer ke halaman pembayaran atau tampilkan Snap langsung

Perlu route tambahan, misalnya:

- `/order/{slug}/payment/{order}`
- `/order/payment/{orderNumber}/status`

### 4. Midtrans Service

File terdampak:

- `app/Services/MidtransService.php`

Perubahan:

- Untuk order publik, data customer harus mengambil dari:
  - `guest_name`
  - `guest_phone`
- Jangan fallback ke creator kasir untuk public order
- Payload item detail tetap bisa memakai item order yang sudah ada

### 5. Midtrans Webhook

File terdampak:

- `app/Http/Controllers/MidtransWebhookController.php`

Perubahan:

- Jika order source `public_qr`:
  - payment `paid` -> order `paid`
  - payment `pending` -> order tetap `pending_payment`
  - payment `expire` -> order `expired`
  - payment `cancel` atau `deny` -> order `canceled` atau `failed`
- Jangan pakai rule lama `draft -> served` untuk order publik

Rekomendasi:

- Cabangkan logic webhook:
  - POS internal
  - public QR ordering

### 6. UI Customer

File terdampak:

- `resources/views/public/ordering/show.blade.php`

Perubahan:

- Ganti tombol `Kirim Pesanan` menjadi `Lanjut ke Pembayaran`
- Setelah submit, customer diarahkan ke halaman payment atau payment status
- Halaman payment perlu menampilkan:
  - nomor order
  - total pembayaran
  - status pembayaran
  - tombol bayar ulang jika masih pending
  - pesan jika transaksi expired

### 7. UI Internal

File terdampak:

- `app/Filament/Resources/PublicOrders/...`

Perubahan:

- Hapus aksi `Konfirmasi` manual untuk alur lama
- Ganti dengan aksi operasional:
  - `Mulai Proses`
  - `Tandai Selesai`
  - `Batalkan`
- Tambahkan filter status yang jelas:
  - pending payment
  - paid
  - processing
  - served
  - expired
  - canceled

### 8. Monitoring dan Keamanan

Perlu dijaga:

- Order `pending_payment` tidak tampil sebagai order siap proses
- Customer tidak kehilangan akses ke halaman payment jika menutup browser
- Webhook Midtrans selalu bisa menemukan order dan payment terkait
- QR nonaktif tidak bisa dipakai
- Stok tetap divalidasi sebelum order dibuat

## Rekomendasi Bisnis dan Operasional

Model terbaik untuk fitur ini adalah:

- `pay-first`
- order publik baru dianggap valid setelah pembayaran sukses
- outlet hanya memproses order yang statusnya `paid`

Keuntungan:

- antrean outlet tidak tercampur order yang belum bayar
- lebih cocok untuk pemesanan mandiri via QR
- minim kebingungan antara kasir dan customer
- memanfaatkan integrasi Midtrans yang sudah ada

## Rencana Kerja

### Fase 1: Rapikan status order dan payment

- Tambah konstanta status baru di model order
- Perbarui label status
- Pastikan flow POS internal tidak rusak
- Buat migration jika ada penyesuaian field tambahan

### Fase 2: Refactor public ordering service

- Ubah order publik agar dibuat dengan status `pending_payment`
- Buat payment `pending`
- Simpan relasi order dan payment Midtrans
- Siapkan generator Snap untuk public order

### Fase 3: Tambah halaman pembayaran publik

- Buat halaman payment atau payment status
- Tampilkan Snap atau redirect URL Midtrans
- Tampilkan status pembayaran untuk customer
- Tampilkan aksi retry bila masih pending

### Fase 4: Perbarui webhook Midtrans

- Cabangkan logic untuk `public_qr`
- Map status payment ke status order publik
- Cegah order publik langsung menjadi `served` saat payment sukses

### Fase 5: Perbarui panel internal

- Ubah resource public order
- Hapus tombol konfirmasi manual
- Tambah aksi operasional:
  - mulai proses
  - tandai selesai
  - batalkan
- Tambah badge dan filter status

### Fase 6: Perbarui UX customer

- Ganti CTA dari `Kirim Pesanan` menjadi `Lanjut ke Pembayaran`
- Tambah feedback yang jelas untuk:
  - pending payment
  - paid
  - expired
  - canceled

### Fase 7: Testing

Tambahkan test untuk:

- pembuatan order publik dengan status `pending_payment`
- pembuatan payment pending
- payment Midtrans sukses mengubah order ke `paid`
- payment Midtrans expired mengubah order ke `expired`
- order `paid` bisa dipindah ke `processing`
- order `processing` bisa dipindah ke `served`
- customer tidak bisa order dari QR nonaktif

## Urutan Implementasi yang Disarankan

1. Rapikan status order dan webhook
2. Refactor public ordering service
3. Tambah halaman payment publik
4. Perbarui resource internal
5. Tambah test otomatis

## Kesimpulan

Perbaikan terbaik untuk fitur QR public ordering adalah mengubah alurnya menjadi:

- customer pesan
- customer langsung lanjut bayar Midtrans
- payment sukses mengesahkan order
- outlet memproses order yang sudah dibayar
- order baru menjadi `served` saat benar-benar selesai

Pendekatan ini paling konsisten dengan kebutuhan self-ordering dan paling aman untuk menjaga kejelasan status pesanan di sistem.
