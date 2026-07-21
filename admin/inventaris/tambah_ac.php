<?php
require_once __DIR__ . '/../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: ../../index.php');
  exit;
}

$id_lab = $_POST['id_lab'] ?? $_GET['id_lab'] ?? null;
if (!$id_lab) { header('Location: ../lab/data_lab.php'); exit; }
$id_lab = mysqli_real_escape_string($koneksi, $id_lab);

$res = mysqli_query($koneksi, "SELECT COALESCE(MAX(nomor_ac),0) AS max_no FROM inventaris_ac WHERE id_lab = '$id_lab'");
$row = mysqli_fetch_assoc($res);
$nomorBaru = (int)$row['max_no'] + 1;

mysqli_query($koneksi, "INSERT INTO inventaris_ac (id_lab, nomor_ac, kondisi) VALUES ('$id_lab', '$nomorBaru', 'normal')");

header('Location: inventaris_lab.php?id_lab=' . urlencode($id_lab));
exit;