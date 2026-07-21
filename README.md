# Sistem Peminjaman Lab

Sistem reservasi laboratorium untuk Program Studi Informatika — mahasiswa dapat mengajukan peminjaman lab lengkap dengan pemilihan kursi, sementara admin mengelola data lab, inventaris, mahasiswa, dan persetujuan peminjaman.

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

## Catatan Migrasi Struktur

Project ini sebelumnya berupa 50+ file `.php` yang semuanya berada langsung di root folder (flat structure), sehingga cukup sulit ditelusuri. Struktur di atas mengelompokkan file berdasarkan modul/fitur agar lebih mudah dirawat. Perubahan yang dilakukan saat reorganisasi:

- Semua `include 'koneksi.php';` diganti menjadi `require_once __DIR__ . '/path/relatif/config/koneksi.php';` — lebih aman karena tidak bergantung pada *current working directory* saat file dieksekusi, dan `require_once` mencegah file ter-include dua kali.
- Seluruh `href`, `action`, `fetch()`, `header('Location: ...')`, dan `window.location` yang menunjuk ke file lain sudah disesuaikan otomatis ke path relatif yang baru. Sudah diverifikasi: **307 link diperiksa, semuanya resolve ke file yang benar-benar ada.**
- Ditemukan satu link rusak peninggalan dari sebelum reorganisasi: `detail_riwayat_inventaris.php` mengarah ke `export_riwayat.php` (file yang tidak pernah ada — kemungkinan salah ketik). Sudah diperbaiki mengarah ke `export_riwayat_inventaris.php` yang memang dimaksud.
- `data_pinjam.php` dan `pinjam_lab.php` adalah file kosong (0 byte) dan tidak direferensikan file manapun. Dipindahkan ke `_unused_kosong/` alih-alih dihapus, supaya tidak ada yang hilang tanpa sepengetahuan Anda — aman dihapus kalau memang tidak terpakai.
