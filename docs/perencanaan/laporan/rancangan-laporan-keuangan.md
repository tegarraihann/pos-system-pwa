# Rancangan Laporan Keuangan

Tanggal: 2026-05-17

## Tujuan

Dokumen ini menyusun rancangan awal modul laporan keuangan untuk sistem POS. Fokus utamanya adalah:

- menyelaraskan laporan dengan istilah dan struktur yang lazim dipakai dalam akuntansi
- memisahkan laporan operasional dari laporan keuangan
- menetapkan fondasi data yang wajib tersedia sebelum laporan keuangan dibangun
- menyiapkan roadmap implementasi yang realistis terhadap struktur sistem saat ini

## Prinsip Dasar

1. Laporan operasional dan laporan keuangan tidak boleh dicampur.
2. Penjualan dan pembayaran bukan laporan keuangan final, tetapi sumber data pendukung.
3. Laporan laba rugi yang layak harus bertumpu pada HPP yang sahih.
4. HPP tidak boleh dihitung secara kasar dari harga jual, tetapi dari konsumsi bahan baku dan nilai persediaan.
5. Bahasa laporan harus mengikuti istilah akuntansi yang baku dan konsisten.

## Ruang Lingkup

Rancangan ini difokuskan untuk kebutuhan usaha F&B atau retail kecil yang memakai:

- POS internal
- pemesanan via QR publik
- manajemen stok bahan baku
- resep per menu atau varian menu
- sesi kasir dan absensi

Rancangan ini belum mengasumsikan adanya modul akuntansi penuh seperti jurnal umum, buku besar, rekonsiliasi bank, atau konsolidasi multi-entitas.

## Tujuan Akuntansi Modul Ini

Modul laporan keuangan nantinya diharapkan mampu menjawab pertanyaan berikut:

- berapa penjualan bersih pada suatu periode
- berapa harga pokok penjualan pada periode yang sama
- berapa laba kotor yang dihasilkan
- berapa beban operasional yang dikeluarkan
- berapa laba bersih periode berjalan
- berapa kontribusi masing-masing kategori atau produk terhadap profitabilitas

## Laporan Keuangan yang Disarankan

### 1. Laporan Harga Pokok Penjualan

Ini adalah fondasi utama untuk laporan laba rugi.

Komponen yang disarankan:

- Persediaan Awal
- Pembelian Bersih
- Barang Tersedia untuk Dijual / Digunakan
- Persediaan Akhir
- Harga Pokok Penjualan

Dalam konteks POS berbasis resep, sistem juga perlu mampu menghasilkan:

- HPP per item terjual
- HPP per varian menu
- HPP per kategori menu
- HPP total periode

### 2. Laporan Laba Rugi

Ini adalah laporan keuangan prioritas pertama yang layak dibangun setelah fondasi HPP tersedia.

Struktur yang disarankan:

- Penjualan Bruto
- Diskon Penjualan
- Penjualan Bersih
- Harga Pokok Penjualan
- Laba Kotor
- Beban Operasional
- Laba Usaha
- Pendapatan Lain-lain
- Beban Lain-lain
- Laba Sebelum Pajak
- Pajak
- Laba Bersih Periode Berjalan

### 3. Laporan Beban Operasional

Laporan ini berfungsi sebagai laporan pendukung laba rugi.

Kelompok beban yang disarankan:

- Beban Gaji
- Beban Sewa
- Beban Listrik, Air, dan Internet
- Beban Administrasi
- Beban Pemasaran
- Beban Transportasi
- Beban Pemeliharaan
- Beban Lain-lain

### 4. Laporan Arus Kas Sederhana

Ini disarankan sebagai tahap lanjutan, bukan tahap awal.

Struktur sederhananya:

- Arus Kas dari Aktivitas Operasi
- Arus Kas dari Aktivitas Investasi
- Arus Kas dari Aktivitas Pendanaan
- Kenaikan / Penurunan Kas
- Saldo Awal Kas
- Saldo Akhir Kas

### 5. Laporan Posisi Keuangan / Neraca

Ini adalah target jangka menengah atau panjang.

Struktur utamanya:

- Aset
- Liabilitas
- Ekuitas

Untuk tahap sekarang, laporan ini belum realistis dibangun tanpa fondasi akun dan pencatatan transaksi akuntansi yang lebih lengkap.

## Terminologi Akuntansi yang Wajib Dipakai

Agar laporan terlihat profesional dan mudah dipahami oleh owner, akuntan, maupun auditor internal, istilah berikut disarankan dipakai secara konsisten:

- Penjualan Bruto
- Diskon Penjualan
- Penjualan Bersih
- Harga Pokok Penjualan
- Laba Kotor
- Beban Operasional
- Laba Usaha
- Pendapatan Lain-lain
- Beban Lain-lain
- Laba Sebelum Pajak
- Pajak
- Laba Bersih
- Persediaan Awal
- Persediaan Akhir
- Kas dan Setara Kas

Istilah yang sebaiknya dihindari:

- omzet untung
- profit jualan
- hasil kasir
- sisa uang toko

## Fondasi Data yang Wajib Ada

### 1. Integrasi Resep ke Konsumsi Bahan

Sistem saat ini sudah memiliki resep, tetapi resep belum terhubung penuh ke perhitungan laba rugi.

Yang dibutuhkan:

- saat order valid, sistem membaca resep item yang terjual
- bahan baku berkurang otomatis berdasarkan kuantitas resep
- sistem menyimpan nilai biaya konsumsi bahan untuk item tersebut

### 2. Snapshot HPP per Order Item

Setiap transaksi penjualan perlu menyimpan snapshot biaya pada saat kejadian.

Minimal data yang diperlukan:

- `cost_snapshot` per order item
- `gross_profit_snapshot` per order item
- `margin_percent_snapshot` opsional

Tanpa snapshot, laba historis akan berubah-ubah jika harga bahan berubah.

### 3. Penilaian Persediaan

Sistem perlu punya pendekatan valuasi yang konsisten.

Pilihan yang realistis:

- moving average
- FIFO
- simple latest cost snapshot untuk tahap awal

Rekomendasi awal:

- gunakan pendekatan biaya rata-rata bergerak atau snapshot biaya pembelian terbaru yang terkontrol
- dokumentasikan metode yang dipilih karena akan memengaruhi HPP dan laba

### 4. Pencatatan Beban Operasional

Modul beban perlu disiapkan karena tanpa beban, laporan laba rugi hanya berhenti di laba kotor.

Data minimal:

- tanggal
- akun beban
- nominal
- deskripsi
- metode pembayaran
- user pencatat
- lampiran opsional

### 5. Klasifikasi Akun / COA

Untuk menjaga kerapihan dan skalabilitas, sebaiknya dibuat Chart of Accounts sederhana.

Akun minimal yang disarankan:

- Kas
- Bank
- Persediaan
- Penjualan
- Diskon Penjualan
- Harga Pokok Penjualan
- Beban Gaji
- Beban Sewa
- Beban Utilitas
- Beban Administrasi
- Modal
- Laba Ditahan

## Modul yang Perlu Dibangun

### 1. Master Akun / Chart of Accounts

Struktur minimal:

- kode akun
- nama akun
- kategori akun
- kelompok laporan
- saldo normal
- status aktif

### 2. Modul Beban Operasional

Fungsi:

- input beban manual
- klasifikasi per akun beban
- pelacakan pembayaran beban
- dasar penyusunan laporan beban dan laba rugi

### 3. Engine HPP

Fungsi:

- tarik resep dari menu atau varian
- hitung konsumsi bahan
- kurangi stok bahan
- simpan snapshot biaya per transaksi

### 4. Modul Laporan Keuangan

Output awal yang disarankan:

- Laporan HPP
- Laporan Laba Rugi
- Laporan Beban Operasional
- Ringkasan Keuangan Outlet

### 5. Export PDF

Semua laporan keuangan sebaiknya mendukung:

- tampilan PDF formal
- periode laporan
- identitas outlet atau sistem
- tanggal dan user pencetak

## Tahapan Implementasi yang Disarankan

### Tahap 1

- finalisasi struktur resep dan hubungan ke stok
- bangun mekanisme konsumsi bahan otomatis saat transaksi
- simpan snapshot HPP per order item

### Tahap 2

- bangun modul akun sederhana
- bangun modul beban operasional
- siapkan query keuangan dasar

### Tahap 3

- bangun Laporan HPP
- bangun Laporan Laba Rugi
- bangun Laporan Beban Operasional

### Tahap 4

- bangun Ringkasan Keuangan Outlet
- tambahkan export PDF
- tambahkan filter periode, kategori, dan sumber order

### Tahap 5

- evaluasi kesiapan arus kas sederhana
- siapkan fondasi neraca bila bisnis sudah memerlukan

## Role dan Hak Akses

### Kasir

Tidak disarankan mendapat akses ke laporan keuangan penuh.

### Admin

Boleh melihat:

- laporan HPP
- laporan laba rugi sederhana
- laporan beban operasional

Jika bisnis menganggap data laba sensitif, akses admin perlu dibatasi lebih lanjut.

### Super Admin / Owner

Boleh melihat seluruh laporan keuangan.

### Supervisor / Manager Outlet

Opsional, dengan scope outlet yang menjadi tanggung jawabnya.

## Risiko dan Batasan Saat Ini

1. Sistem saat ini belum mempunyai HPP per transaksi.
2. Resep belum menjadi sumber perhitungan biaya historis yang final.
3. Modul beban operasional belum tersedia.
4. COA belum tersedia.
5. Neraca dan arus kas penuh belum layak dibuat pada tahap awal.

## Rekomendasi Final

Untuk codebase saat ini, urutan yang paling sehat adalah:

1. bangun engine HPP
2. bangun modul beban operasional
3. bangun Laporan HPP
4. bangun Laporan Laba Rugi
5. bangun laporan keuangan lanjutan setelah fondasi cukup stabil

## Status

Dokumen ini adalah rancangan awal laporan keuangan. Dokumen ini dipakai sebagai acuan sebelum implementasi modul HPP, beban operasional, dan laporan laba rugi dimulai.
