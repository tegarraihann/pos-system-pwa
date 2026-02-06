# Panduan Reverb + Domain Publik (Cloudflared Tunnel)

Dokumen ini fokus ke **Reverb + domain publik** (tanpa setup aplikasi), karena instalasi dasar sudah dijelaskan di README.

---

## Langkah 1 — Login Cloudflared
Jika belum login, jalankan:
```bash
cloudflared tunnel login
```
Pilih domain yang akan dipakai.

---

## Langkah 2 — Buat tunnel
Buat tunnel baru:
```bash
cloudflared tunnel create pos-system
```
Setelah berhasil, akan ada file JSON di:
```
C:\Users\<user>\.cloudflared\
```
Contoh: `pos-system.json`

---

## Langkah 3 — Hubungkan subdomain
Jalankan:
```bash
cloudflared tunnel route dns pos-system pos.livedemo.web.id
cloudflared tunnel route dns pos-system reverb.livedemo.web.id
```
> Ganti `livedemo.web.id` dengan domain kamu.

---

## Langkah 4 — Pastikan config.yml sudah ada
Kamu sudah menaruh `config.yml`. Pastikan lokasinya:
```
C:\Users\<user>\.cloudflared\config.yml
```
Isi contohnya:
```
tunnel: pos-system
credentials-file: C:\Users\<user>\.cloudflared\pos-system.json

ingress:
  - hostname: pos.livedemo.web.id
    service: http://127.0.0.1:8000
  - hostname: reverb.livedemo.web.id
    service: http://127.0.0.1:8081
  - service: http_status:404
```

---

## Langkah 5 — Update `.env`
Isi bagian ini agar sesuai domain publik:
```
APP_URL=https://pos.livedemo.web.id
REVERB_HOST=reverb.livedemo.web.id
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8081
```

---

## Langkah 6 — Jalankan semuanya
Jalankan 3 terminal:

1. **Laravel**
```bash
php artisan serve --port=8000
```

2. **Reverb**
```bash
php artisan reverb:start
```

3. **Tunnel**
```bash
cloudflared tunnel run pos-system
```

---

## Cara cek berhasil
1. Buka:
```
https://pos.livedemo.web.id/admin
```
2. Buat order baru.
3. Di Kitchen Display, order harus muncul otomatis tanpa refresh.

---

## Catatan penting
- Jika hanya **1 laptop aktif**, boleh pakai subdomain yang sama.
- Jika **lebih dari 1 laptop aktif**, buat tunnel + subdomain baru.
- Jika realtime mati, cek Reverb + tunnel masih jalan.
