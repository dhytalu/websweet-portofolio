# WSS Portofolio

Plugin WordPress untuk mengimpor Portofolio dan Kategori (taxonomy `jenis-web`) dari API WebSweetStudio (WSS) ke situs lokal, lengkap dengan progress bar impor (persentase), meta box di layar edit, template single & arsip, serta tombol Live Preview dan Order via WhatsApp.

## Fitur

- Import Portofolio dan Kategori dari API WSS.
- Progress impor real‑time (overlay dengan persentase) via AJAX & transient.
- Meta box "WSS Portofolio Meta" di layar edit Portofolio (Remote ID, Live Preview, Remote Last Modified).
- Template bawaan untuk:
  - Single Portofolio: dua kolom (Bootstrap), tombol Live Preview, Order via WhatsApp.
  - Arsip Portofolio: grid item dengan thumbnail, excerpt, tombol Detail, Live Preview, Order via WhatsApp.
- Enqueue Bootstrap CSS otomatis di halaman single & arsip Portofolio.
- Pengaturan WhatsApp (nomor & template pesan) dengan placeholder dinamis.

## Kebutuhan Sistem

- WordPress 5.8+ (disarankan terbaru)
- PHP 7.4+ (disarankan 8.0+)

## Instalasi

1. Salin folder plugin `wss-portofolio` ke `wp-content/plugins/`.
2. Aktifkan plugin di Dashboard → Plugins.
3. Pastikan CPT `portofolio` tampil di menu dan halaman admin Tools → WSS Portofolio tersedia.

## Penggunaan

1. Buka `Dashboard → Portofolio → WSS Portofolio`.
2. Bagian Pengaturan:
   - `Access Key`: token API WSS.
   - `Per Page`: jumlah item per halaman saat fetch portofolio.
   - `Cache TTL`: durasi cache response API (detik).
   - `Nomor WhatsApp`: nomor dalam format internasional tanpa `+` atau spasi (contoh: `6281234567890`).
   - `Template Pesan`: template pesan WhatsApp. Placeholder yang didukung:
     - `{title}`: judul portofolio
     - `{permalink}`: URL detail portofolio
     - `{live_preview}`: URL live preview (jika ada)
   - Simpan pengaturan dengan tombol "Simpan".
3. Import:
   - Klik "Import Kategori (jenis-web)" untuk mengimpor taxonomy.
   - Klik "Import Portofolio" untuk mengimpor posting portofolio.
   - Opsi "Full import (abaikan last_sync)" untuk memaksa impor penuh.
4. Progress impor:
   - Overlay akan muncul dengan bar & label persentase, diperbarui via polling AJAX.
5. Frontend:
  - Arsip: `/portofolio/` menampilkan daftar portofolio.
  - Single: klik salah satu item untuk melihat layout dua kolom dengan tombol Live Preview & Order via WhatsApp.

### Shortcodes

- `wssp_live_preview`
  - Menampilkan tombol Live Preview berdasarkan meta `_wssp_url_live_preview`.
  - Atribut:
    - `post_id` (opsional): ID post target. Default: post saat ini.
    - `text` (opsional): label tombol. Default: `Live Preview`.
    - `class` (opsional): kelas tambahan Bootstrap. Ditambahkan ke `btn btn-primary`.
    - `target` (opsional): target link. Default: `_blank`.
    - `icon` (opsional): `1`/`0` untuk tampilkan ikon Font Awesome. Default: `1`.
    - `icon_class` (opsional): kelas ikon. Default: `fa fa-eye me-2`.
  - Contoh:
    - `[wssp_live_preview class="btn-sm"]`
    - `[wssp_live_preview post_id="123" text="Lihat Demo" icon="1" icon_class="fa fa-eye me-1"]`

- `wssp_order_whatsapp`
  - Menampilkan tombol Order via WhatsApp menggunakan helper `wssp_get_whatsapp_order_url`.
  - Atribut:
    - `post_id` (opsional): ID post target. Default: post saat ini.
    - `text` (opsional): label tombol. Default: `Order`.
    - `class` (opsional): kelas tambahan Bootstrap. Ditambahkan ke `btn btn-success`.
    - `target` (opsional): target link. Default: `_blank`.
    - `icon` (opsional): `1`/`0` untuk tampilkan ikon Font Awesome. Default: `1`.
    - `icon_class` (opsional): kelas ikon. Default: `fa fa-whatsapp me-2`.
  - Contoh:
    - `[wssp_order_whatsapp class="btn-sm" text="Order via WhatsApp"]`
    - `[wssp_order_whatsapp post_id="123" icon="0"]`

## Detail Teknis

- Konstanta & opsi:
  - `WSSP_OPTION_KEY`: akses key API.
  - `WSSP_LAST_SYNC_OPTION`: timestamp terakhir sinkronisasi.
  - `WSSP_PER_PAGE_OPTION`: pagination saat fetch.
  - `WSSP_CACHE_TTL_OPTION`: TTL cache.
  - `WSSP_WHATSAPP_NUMBER_OPTION`: nomor WhatsApp.
  - `WSSP_WHATSAPP_TEMPLATE_OPTION`: template pesan WhatsApp.
  - `WSSP_PROGRESS_TRANSIENT`: key transient status progres impor.
  - `WSSP_REMOTE_META_KEY`: key meta untuk remote ID.
- Helper WhatsApp:
  - `wssp_get_whatsapp_order_url( $post_id )` membentuk link `https://wa.me/<number>?text=<message>` dengan placeholder diisi otomatis.
- Enqueue CSS:
  - Bootstrap CSS (`5.3.2`) dimuat otomatis pada halaman single dan arsip `portofolio`.
- Template loader:
  - Filter `single_template` & `archive_template` diarahkan ke file dalam folder `templates` plugin.
- Meta box:
  - Ditambahkan lewat `add_meta_box` untuk CPT `portofolio` (read‑only).
- Progress impor:
  - Importer mengupdate transient status selama loop impor, frontend admin melakukan polling.

## Struktur Proyek

```
wss-portofolio/
├── assets/
│   ├── admin.css        # Overlay & progres impor di admin
│   └── admin.js         # AJAX impor & polling progres
├── includes/
│   ├── class-wss-client.php   # Client API WSS
│   └── class-wss-importer.php # Importer posts & terms
├── templates/
│   ├── archive-portofolio.php # Template arsip portofolio
│   └── single-portofolio.php  # Template single portofolio (Bootstrap)
└── wss-portofolio.php         # File utama plugin
```

## Kustomisasi

- Ubah tampilan template di `templates/` sesuai kebutuhan tema Anda.
- Edit `assets/admin.css` untuk menyesuaikan gaya overlay progres.
- Ganti CDN Bootstrap atau menonaktifkannya jika tema sudah memuat Bootstrap.

## Catatan

- Tombol ikon menggunakan kelas Font Awesome (`fa fa-eye`, `fa fa-whatsapp`) opsional. Jika tema belum memuat Font Awesome, Anda bisa:
  - Menghapus kelas ikon dari template, atau
  - Menambahkan enqueue Font Awesome di tema/plugin.
- Jika impor tidak memunculkan progres, cek konsol browser DevTools & log PHP untuk error AJAX.

## Changelog

- 1.0.0
  - Import posts & terms dengan progres.
  - Meta box di layar edit Portofolio.
  - Template single/arsip dengan tombol Live Preview.
  - Pengaturan & tombol Order via WhatsApp.
  - Enqueue Bootstrap CSS untuk tampilan frontend Portofolio.