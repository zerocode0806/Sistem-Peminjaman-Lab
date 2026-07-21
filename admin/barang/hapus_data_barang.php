<?php
require_once __DIR__ . '/../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: ../../index.php');
  exit;
}

if (!isset($_GET['id']) || $_GET['id'] === '') {
  header('Location: data_barang.php');
  exit;
}

$id_barang = $_GET['id'];
$id_lab    = $_GET['id_lab'] ?? '';

$stmt = mysqli_prepare($koneksi, "DELETE FROM data_barang WHERE id_barang = ?");
mysqli_stmt_bind_param($stmt, "s", $id_barang);
mysqli_stmt_execute($stmt);

if ($id_lab !== '') {
    header('Location: data_barang.php?id_lab=' . urlencode($id_lab));
} else {
    header('Location: data_barang.php');
}
exit;