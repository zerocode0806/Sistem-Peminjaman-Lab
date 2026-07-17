<?php
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    $id = mysqli_real_escape_string($koneksi, $id);
    // FK ON DELETE CASCADE pada inventaris_ac & inventaris_meja
    // akan otomatis menghapus data inventaris terkait lab ini.
    mysqli_query($koneksi, "DELETE FROM data_lab WHERE id_lab = '$id'");
}

header('Location: data_lab.php');
exit;