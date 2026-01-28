# Rencana Kerja: Peningkatan UI/UX Halaman POS Kasir

Dokumen ini menguraikan langkah-langkah untuk meningkatkan antarmuka dan pengalaman pengguna pada halaman POS Kasir di `/admin/pos-cashier`.

## Tujuan
Meningkatkan estetika, konsistensi responsif, dan kemudahan penggunaan bagi kasir, serta memastikan kode lebih bersih dengan menggunakan Tailwind CSS murni.

## Fase 1: Refactoring Visual & Styling (Blade)
Mengganti *raw CSS* dengan *Tailwind Utility Classes* agar konsisten dengan tema Filament.

- [ ] **Hapus Tag `<style>`**: Menghapus blok CSS custom di `resources/views/filament/pages/pos-cashier.blade.php`.
- [ ] **Implementasi Layout Utama**:
    - Ubah container utama menjadi grid responsif menggunakan class Tailwind (`grid grid-cols-1 lg:grid-cols-3 gap-6`).
    - Pastikan kompatibilitas Light/Dark mode menggunakan semantic colors Filament (misal: `bg-white dark:bg-gray-900`).
- [ ] **Redesain Kartu Produk**:
    - Perbaiki styling kartu agar lebih modern.
    - **Placeholder Image**: Ganti teks "No Image" dengan ikon SVG (Heroicon) yang estetis dengan background abu-abu lembut.
    - Tambahkan efek hover yang halus (`hover:shadow-lg`).

## Fase 2: Peningkatan UX Cart (Keranjang)
Memudahkan kasir dalam mengelola item di keranjang.

- [ ] **Input Kuantitas Manual**:
    - Ubah tampilan Qty agar memungkinkan input angka secara langsung, selain tombol `+` dan `-`.
    - Update `PosCashier.php` untuk menangani method `updateQty($id, $val)`.
- [ ] **Ikon Hapus**:
    - Ganti tombol teks "Hapus" yang memakan tempat dengan **Action Icon** (Trash Icon) berwarna merah (`color="danger"`).
- [ ] **Empty State**:
    - Tambahkan ikon/ilustrasi sederhana saat Cart kosong atau Produk tidak ditemukan, agar tidak hanya berupa teks.

## Fase 3: Feedback & Interaktivitas (PHP)
Memberikan umpan balik visual kepada pengguna.

- [ ] **Notifikasi Toast**:
    - Tambahkan `Notification::make()->success()->send()` pada method `addToCart` di `PosCashier.php` agar kasir tahu item berhasil masuk tanpa harus melihat cart setiap saat.
- [ ] **Auto-Focus Barcode**:
    - Pastikan input barcode memiliki atribut `autofocus`.

## Fase 4: Checkout UI Flow
Persiapan antarmuka pembayaran.

- [ ] **Tombol Bayar**:
    - Pastikan tombol terlihat *prominent* (menonjol).
    - Siapkan styling untuk modal pembayaran (Placeholder untuk fitur selanjutnya).

---

## Technical Notes
- **File Target**:
    - `resources/views/filament/pages/pos-cashier.blade.php`
    - `app/Filament/Pages/PosCashier.php`
- **Dependencies**: Menggunakan komponen bawaan Filament (`x-filament::button`, `x-filament::input`) dan Tailwind CSS.
