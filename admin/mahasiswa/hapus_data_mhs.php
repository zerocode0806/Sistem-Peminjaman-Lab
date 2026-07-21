<?php
session_start();
require_once __DIR__ . '/../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}

$nim = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

if ($nim === '') {
    header('Location: data_mhs.php');
    exit;
}

/* 🔎 Cek apakah mahasiswa masih memiliki riwayat peminjaman */
$cekPinjam = mysqli_query($koneksi, "
    SELECT id_data FROM data_pinjam WHERE nim = '$nim' LIMIT 1
");

if ($cekPinjam && mysqli_num_rows($cekPinjam) > 0) {
    header('Location: data_mhs.php?error=has_pinjam');
    exit;
}

$delete = mysqli_query($koneksi, "DELETE FROM mahasiswa WHERE nim = '$nim'");

if ($delete && mysqli_affected_rows($koneksi) > 0) {
    header('Location: data_mhs.php?msg=deleted');
} else {
    header('Location: data_mhs.php?error=not_found');
}
exit;