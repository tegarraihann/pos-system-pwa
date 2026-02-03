# Panduan Reverb (Realtime) Setelah Clone Project

Dokumen ini menjelaskan cara menyalakan fitur realtime (Reverb) dengan bahasa sederhana.

## Apa fungsi Reverb?
Reverb membuat halaman seperti Kitchen Display update otomatis tanpa refresh.

## Langkah singkat (setelah clone)
1) Jalankan aplikasi Laravel seperti biasa.  
2) Jalankan Reverb di terminal terpisah.  
3) Buka aplikasi di browser, dan pastikan realtime berjalan.  

## Langkah detail
1) **Salin file konfigurasi**
   - Copy `.env.example` menjadi `.env`.

2) **Isi bagian Reverb di `.env`**
   Contoh nilai (sesuai setup publik):
   ```
   REVERB_APP_ID=local
   REVERB_APP_KEY=local
   REVERB_APP_SECRET=local
   REVERB_HOST=reverb.livedemo.web.id
   REVERB_PORT=443
   REVERB_SCHEME=https
   REVERB_SERVER_HOST=0.0.0.0
   REVERB_SERVER_PORT=8081
   ```

3) **Cara membuat APP KEY/SECRET**
   Gunakan perintah ini (sekali saja):
   ```
   php artisan reverb:install
   ```
   Perintah ini akan membuat `REVERB_APP_ID`, `REVERB_APP_KEY`, dan `REVERB_APP_SECRET` otomatis.
   Jika sudah ada, kamu bisa tetap mengganti manual di `.env` sesuai kebutuhan.

4) **Bersihkan cache konfigurasi**
   ```
   php artisan config:clear
   ```

5) **Jalankan Laravel**
   ```
   php artisan serve --port=8000
   ```

6) **Jalankan Reverb**
   ```
   php artisan reverb:start
   ```

## Cara cek realtime berjalan
- Buka halaman Kitchen Display.
- Buat order baru.
- Jika kartu pesanan muncul otomatis, realtime sudah aktif.

## Jika realtime tidak jalan
Periksa hal ini:
- Reverb masih berjalan di terminal.
- Port Reverb sesuai dengan `.env`.
- Tidak ada error di terminal Reverb.

## Catatan penting
Jika kamu memakai domain publik (bukan localhost), ganti:
```
REVERB_HOST=domain-reverb-kamu
REVERB_PORT=443
REVERB_SCHEME=https
```
