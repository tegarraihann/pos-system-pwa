# Integrasi Midtrans (Snap) - POS System

Dokumentasi ini menjelaskan cara setup dan alur pembayaran Midtrans Snap overlay pada modul POS.

## Ringkasan Alur

- Kasir menekan tombol **Bayar** di `/admin/pos-cashier`.
- Sistem membuat **Order** + **Payment** dengan status `pending`.
- Sistem meminta **Snap token** ke Midtrans.
- Frontend membuka **Snap overlay** (snap.js).
- Midtrans mengirim **webhook** ke endpoint aplikasi untuk update status pembayaran.

## Prasyarat

- Akun Midtrans (sandbox atau production).
- Server dapat diakses publik untuk menerima webhook.
- HTTPS aktif untuk URL production.

## Konfigurasi Environment

Tambahkan di `.env`:

```env
MIDTRANS_SERVER_KEY=...
MIDTRANS_CLIENT_KEY=...
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SANITIZE=true
MIDTRANS_3DS=true
```

Lalu bersihkan cache config:

```bash
php artisan config:clear
```

## File Penting

- Konfigurasi: `config/midtrans.php`
- Checkout POS: `app/Filament/Pages/PosCashier.php`
- UI Snap Overlay: `resources/views/filament/pages/pos-cashier.blade.php`
- Webhook: `app/Http/Controllers/MidtransWebhookController.php`
- Route webhook: `routes/web.php`

## Endpoint Webhook

Endpoint yang digunakan:

```
POST /midtrans/notification
```

Catatan:
- CSRF dinonaktifkan khusus untuk endpoint ini.
- Validasi dilakukan menggunakan **signature key** Midtrans.

## Mapping Status Pembayaran

Webhook Midtrans mengubah status `payments.status`:

- `capture` + `fraud_status=accept` -> `paid`
- `settlement` -> `paid`
- `pending` -> `pending`
- `deny` -> `failed`
- `expire` -> `expired`
- `cancel` -> `canceled`

Jika status `paid`, maka `orders.status` otomatis menjadi `queued`.

## Setup di Dashboard Midtrans

1. Login Midtrans dashboard.
2. Pilih **Sandbox** untuk testing.
3. Set **Notification URL** ke:

```
https://domain-kamu/midtrans/notification
```

4. Simpan **Server Key** dan **Client Key** ke `.env`.

## Cara Testing (Sandbox)

- Pastikan `MIDTRANS_IS_PRODUCTION=false`.
- Jalankan aplikasi.
- Buka `/admin/pos-cashier`.
- Tambah item ke cart lalu klik **Bayar**.
- Snap overlay akan muncul.
- Selesaikan pembayaran sesuai instruksi sandbox.

## Catatan Keamanan

- Jangan expose `MIDTRANS_SERVER_KEY` ke frontend.
- Signature key selalu divalidasi di webhook.
- Gunakan HTTPS di production.

## Troubleshooting

- **Snap overlay tidak muncul**:
  - Pastikan `MIDTRANS_CLIENT_KEY` terisi dan `snap.js` termuat.
- **Webhook tidak masuk**:
  - Cek URL publik, HTTPS, dan firewall server.
- **Status tidak berubah**:
  - Periksa log di `storage/logs/laravel.log` dan pastikan signature valid.
