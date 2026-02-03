# PWA & Mode Offline (Pembayaran Cash Saja)

Dokumen ini menjelaskan rencana penggunaan aplikasi sebagai PWA dan perilaku saat offline dengan bahasa sederhana.

## Apa yang akan terjadi jika offline?
- Aplikasi tetap bisa dipakai untuk input order.
- Pembayaran hanya **cash**.
- Order disimpan di perangkat sementara.
- Saat internet kembali, order otomatis dikirim ke server.

## Manfaatnya
- Kasir tetap bisa bekerja saat listrik/internet bermasalah.
- Tidak kehilangan data order.
- Begitu online, data langsung masuk ke sistem.

## Alur sederhana (tanpa istilah teknis)
1) Kasir membuka aplikasi seperti biasa.  
2) Jika internet putus, aplikasi memberi tanda “Offline”.  
3) Kasir tetap bisa input order dan memilih pembayaran cash.  
4) Order disimpan sementara di perangkat.  
5) Saat internet kembali, order otomatis dikirim dan status normal.  

## Batasan yang perlu dipahami
- Pembayaran non‑cash tidak bisa dilakukan saat offline.
- Notifikasi realtime akan berhenti saat offline dan aktif lagi ketika online.

## Kenapa perlu PWA?
PWA membuat aplikasi bisa:
- Diinstall seperti aplikasi biasa.
- Tetap berjalan saat koneksi buruk.
- Lebih cepat dibuka karena ada cache dasar.

