<?php
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

$id_periode = $_GET['id_periode'] ?? null;
$id_lab     = $_GET['id_lab'] ?? null;

if ($id_periode) {
    $id_periode = mysqli_real_escape_string($koneksi, $id_periode);
    // riwayat_ac & riwayat_meja ikut terhapus lewat FK ON DELETE CASCADE
    mysqli_query($koneksi, "DELETE FROM periode_inventaris WHERE id_periode = '$id_periode'");
}

header('Location: riwayat_inventaris.php?id_lab=' . urlencode($id_lab));
exit;