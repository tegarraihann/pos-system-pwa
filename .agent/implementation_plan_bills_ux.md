# Rencana Kerja: Peningkatan UI/UX CRUD Bills

Dokumen ini menguraikan langkah-langkah untuk meningkatkan antarmuka dan pengalaman pengguna pada halaman CRUD Bills di `/admin/bills`.

## Tujuan
Meningkatkan efisiensi input data tagihan dengan mengotomatisasi pengisian form berdasarkan data Order dan memastikan kalkulasi biaya dilakukan secara real-time oleh sistem untuk meminimalkan human error.

## Fase 1: Otomatisasi Form (Smart Form Logic)
Membuat form lebih cerdas dengan mengisi data otomatis saat Order dipilih.

- [ ] **Reactive Order Selection**:
    - Update `BillForm.php`: Tambahkan `live()` pada field `order_id`.
    - Implementasi `afterStateUpdated` untuk `order_id`:
        - Ambil instance `Order` berdasarkan ID yang dipilih.
        - Isi field `subtotal`, `discount_total`, `tax_total`, `service_total`, `grand_total` dengan nilai dari Order.
        - Generate `bill_no` otomatis (format: `BILL-{Ymd}-{Time}`) jika kosong.

## Fase 2: Kalkulasi Real-time (Live Calculation)
Memastikan integritas hitungan biaya.

- [ ] **Reactive Money Fields**:
    - Update field `subtotal`, `discount_total`, `tax_total`, `service_total` menjadi `live(onBlur: true)`.
    - Tambahkan logic `afterStateUpdated` pada field-field tersebut untuk menghitung ulang `grand_total`.
    - **Rumus**: `GrandTotal = (Subtotal - Diskon) + Pajak + Service`.
- [ ] **Read-only Grand Total**:
    - Pertimbangkan membuat field `grand_total` menjadi `readOnly()` atau `disabled()` agar user tidak mengedit manual hasil kalkulasi sistem (opsional, tergantung preferensi user).

## Fase 3: Layout & Structuring
Memperbaiki tata letak agar lebih logis dan mudah dibaca.

- [ ] **Grouping Section**:
    - Pisahkan Informasi Utama (Order, Bill No, Status) dari Rincian Biaya.
    - Gunakan `Section::make('Rincian Biaya')` col-2 atau col-3 untuk field nominal.
- [ ] **Visual Hierarchy**:
    - Letakkan `Grand Total` di posisi yang menonjol (misal: paling bawah kanan atau width full).

## Fase 4: Testing & Verifikasi
- [ ] Buat Bill baru dari Order yang valid.
- [ ] Verifikasi semua field terisi otomatis.
- [ ] Ubah nilai 'Service' secara manual, pastikan 'Grand Total' berubah.

---
**File Target**: `app/Filament/Resources/Bills/Schemas/BillForm.php`
