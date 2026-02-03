# Panduan Setup Filament (Bahasa Sederhana)

Dokumen ini menjelaskan cara menyiapkan Filament agar panel admin bisa dipakai.

## 1) Pastikan aplikasi berjalan
Jalankan aplikasi Laravel seperti biasa.  
Jika halaman utama sudah terbuka, berarti aplikasi siap dipakai.

## 2) Jalankan migrasi database
Tujuannya agar tabel di database lengkap.

## 3) Buat akun admin
Gunakan perintah pembuatan user Filament, lalu isi nama, email, dan password.

## 4) Login ke panel admin
Buka halaman:
```
https://domain-anda/admin/login
```
Masukkan email dan password yang sudah dibuat.

## 5) Jika memakai role/permission
Pastikan user diberi role **admin** atau **super_admin** agar bisa masuk ke panel.

## Tips singkat
- Jika tidak bisa login, cek apakah database sudah terisi dan user sudah dibuat.
- Jika panel tidak muncul, pastikan URL `APP_URL` di `.env` sudah benar.

