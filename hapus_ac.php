<?php
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

$id_ac  = $_GET['id_ac']  ?? null;
$id_lab = $_GET['id_lab'] ?? null;

if ($id_ac && $id_lab) {
    $id_ac = mysqli_real_escape_string($koneksi, $id_ac);
    mysqli_query($koneksi, "DELETE FROM inventaris_ac WHERE id_ac = '$id_ac'");
}

header('Location: inventaris_lab.php?id_lab=' . urlencode($id_lab));
exit;