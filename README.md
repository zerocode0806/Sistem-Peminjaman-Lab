# Sistem Manajemen Lab

Sistem reservasi laboratorium untuk Program Studi Informatika — mahasiswa dapat mengajukan peminjaman ruang lab (lengkap dengan pemilihan kursi) maupun peminjaman barang/alat inventaris lab, sementara admin mengelola data lab, inventaris, mahasiswa, akun admin, dan proses persetujuan peminjaman dari awal sampai pengembalian.

## Daftar Isi
- [Fitur](#fitur)
- [Struktur Folder](#struktur-folder)
- [Cara Setup](#cara-setup)
- [Akun Demo](#akun-demo)
- [Panduan Penggunaan](#panduan-penggunaan)
- [Catatan Migrasi Struktur](#catatan-migrasi-struktur)

## Fitur

### Untuk Mahasiswa
- **Registrasi & login mandiri** menggunakan NIM dan password.
- **Ajukan peminjaman ruang lab** — pilih laboratorium, tanggal, jam mulai/selesai, lalu pilih kursi lewat denah interaktif (kursi yang bentrok jadwal otomatis ditandai tidak tersedia).
- **Ajukan peminjaman barang/alat** — pilih barang dari inventaris lab (misal proyektor, router, VR) beserta jumlah yang dibutuhkan.
- **Pantau status pengajuan** secara real-time: `Menunggu` → `Disetujui`/`Ditolak` → `Selesai`.
- **Riwayat & arsip peminjaman** — lihat semua pengajuan yang pernah dibuat, termasuk yang sudah selesai atau ditolak.

### Untuk Admin
- **Dashboard** — ringkasan aktivitas peminjaman dan status lab/barang terkini.
- **Data Laboratorium** — tambah, ubah, hapus data lab beserta status ketersediaan dan kapasitas kursi.
- **Inventaris Lab** — pencatatan aset tetap ruangan (AC, meja, dll.), termasuk riwayat pemeriksaan dan pengelolaan periode inventarisasi berkala.
- **Data Barang** — tambah, ubah, hapus barang/alat yang dimiliki tiap lab beserta stoknya.
- **Data Mahasiswa** — kelola akun mahasiswa yang terdaftar di sistem.
- **Akun Admin** — tambah/ubah/hapus akun admin lain (dengan proteksi tidak bisa menghapus akun sendiri).
- **Proses Peminjaman**:
  - **Approve/Tolak** pengajuan yang berstatus `Menunggu`.
  - **Detail Peminjaman** — lihat data lengkap peminjam, lab/barang yang diajukan, dan ubah status.
  - **Checkout** — tandai peminjaman `Disetujui` sebagai `Selesai` sekaligus mengembalikan stok lab/barang secara otomatis.
  - **Riwayat (Ongoing)** — pantau peminjaman yang masih berjalan.
  - **Arsip** — peminjaman yang sudah `Selesai` atau `Ditolak`.

## Struktur Folder
```
Sistem-Peminjaman-Lab/
├── index.php                  # Landing page (pilih akun mahasiswa/admin)
├── config/
│   └── koneksi.php            # Konfigurasi koneksi database
├── database/
│   └── peminjaman-lab.sql     # Skema & seed database (import ini di phpMyAdmin)
├── auth/
│   ├── login_admin.php
│   ├── login_mhs.php
│   ├── logout.php
│   └── logout_mhs.php
├── ajax/                      # Endpoint yang dipanggil lewat fetch() / JS
│   ├── ajax_dashboard.php
│   ├── ajax_dashboard_mhs.php
│   └── cek_kursi.php
├── admin/
│   ├── dashboard.php
│   ├── lab/                   # CRUD data laboratorium
│   ├── inventaris/            # Inventaris AC, meja, riwayat & periode inventarisasi
│   ├── barang/                # CRUD data barang per lab
│   ├── mahasiswa/             # CRUD data mahasiswa (sisi admin)
│   ├── akun_admin/            # CRUD akun admin
│   └── peminjaman/            # Approve, detail, checkout, riwayat, arsip peminjaman
├── mahasiswa/
│   ├── dashboard_mhs.php
│   ├── tambah_data_pinjam_mhs.php
│   ├── detail_pinjam_mhs.php
│   ├── riwayat_pinjam_mhs.php
│   └── arsip_peminjaman_mhs.php
└── _unused_kosong/             # File 0 byte dari project lama, belum dipakai di mana pun
    ├── data_pinjam.php
    └── pinjam_lab.php
```

## Cara Setup
1. Import `database/peminjaman-lab.sql` ke MySQL (phpMyAdmin, atau `mysql -u root -p peminjaman-lab < database/peminjaman-lab.sql`).
2. Sesuaikan kredensial database di `config/koneksi.php` jika perlu (default: host `localhost`, user `root`, tanpa password, database `peminjaman-lab`).
3. Arahkan document root server (Laragon/XAMPP) ke folder `Sistem-Peminjaman-Lab/` ini. Karena `index.php` ada di root, halaman utama otomatis terbuka saat mengakses domain/vhost-nya.
4. Semua link antar halaman menggunakan **path relatif** (bukan path absolut dari domain), jadi project ini tetap berfungsi baik diakses dari root domain (`http://labsystem.test/`) maupun dari subfolder (`http://localhost/Sistem-Peminjaman-Lab/`).

## Akun Demo

Setelah mengimpor `database/peminjaman-lab.sql`, gunakan akun berikut untuk login dan mencoba sistem:

| Peran | Username / NIM | Password |
|---|---|---|
| Admin | `namacantik` | `namacantik123` |
| Mahasiswa | `buat akun baru saja` | `buat akun baru saja` |

> ⚠️ **Ini akun demo untuk keperluan testing lokal saja.** Jangan pakai kombinasi username/password ini di server yang bisa diakses publik — ganti password akun admin lewat menu **Akun Admin** dan minta mahasiswa mengganti password masing-masing setelah setup awal selesai.

## Panduan Penggunaan

### Sebagai Mahasiswa
1. Buka halaman utama, pilih kartu **Mahasiswa**.
2. Kalau belum punya akun, klik **Daftar** dan isi NIM, nama, no. telepon, alamat, dan password. Kalau sudah punya, langsung **Masuk** dengan NIM dan password.
3. Di dashboard, klik **Ajukan Peminjaman**.
4. Pilih jenis peminjaman:
   - **Ruang Lab** — pilih lab, tanggal, jam, lalu klik kursi yang tersedia pada denah.
   - **Barang/Alat** — pilih barang dari daftar, lalu masukkan jumlah yang dibutuhkan.
5. Klik **Kirim Pengajuan**. Status awal akan menjadi `Menunggu` persetujuan admin.
6. Pantau status pengajuan lewat menu **Riwayat** (yang masih berjalan) atau **Arsip** (yang sudah selesai/ditolak).

### Sebagai Admin
1. Buka halaman utama, pilih kartu **Admin**, lalu masuk dengan username dan password.
2. Kelola data master lewat sidebar: **Laboratorium**, **Data Barang**, **Mahasiswa**, **Akun Admin**.
3. Untuk memproses pengajuan yang masuk:
   - Buka **Peminjaman → Ongoing**, pilih pengajuan berstatus `Menunggu`.
   - Klik **Detail**, lalu pilih **Setujui** atau **Tolak**.
4. Setelah peminjaman selesai dipakai (status `Disetujui`), buka halaman **Checkout**, lalu klik **Tandai Selesai & Kembalikan Stok** — stok lab/barang otomatis bertambah kembali.
5. Riwayat lengkap (selesai/ditolak) bisa dilihat di menu **Arsip**.

## Catatan Migrasi Struktur
Project ini sebelumnya berupa 50+ file `.php` yang semuanya berada langsung di root folder (flat structure), sehingga cukup sulit ditelusuri. Struktur di atas mengelompokkan file berdasarkan modul/fitur agar lebih mudah dirawat. Perubahan yang dilakukan saat reorganisasi:
- Semua `include 'koneksi.php';` diganti menjadi `require_once __DIR__ . '/path/relatif/config/koneksi.php';` — lebih aman karena tidak bergantung pada *current working directory* saat file dieksekusi, dan `require_once` mencegah file ter-include dua kali.
- Seluruh `href`, `action`, `fetch()`, `header('Location: ...')`, dan `window.location` yang menunjuk ke file lain sudah disesuaikan otomatis ke path relatif yang baru. Sudah diverifikasi: **307 link diperiksa, semuanya resolve ke file yang benar-benar ada.**
- Ditemukan satu link rusak peninggalan dari sebelum reorganisasi: `detail_riwayat_inventaris.php` mengarah ke `export_riwayat.php` (file yang tidak pernah ada — kemungkinan salah ketik). Sudah diperbaiki mengarah ke `export_riwayat_inventaris.php` yang memang dimaksud.
- `data_pinjam.php` dan `pinjam_lab.php` adalah file kosong (0 byte) dan tidak direferensikan file manapun. Dipindahkan ke `_unused_kosong/` alih-alih dihapus, supaya tidak ada yang hilang tanpa sepengetahuan Anda — aman dihapus kalau memang tidak terpakai.
