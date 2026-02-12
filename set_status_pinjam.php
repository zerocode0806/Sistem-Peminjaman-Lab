<?php
include 'koneksi.php';

if (!isset($_GET['id'], $_GET['status'])) {
    header("Location: index.php");
    exit;
}

$id     = (int) $_GET['id'];
$status = $_GET['status'];

/* Validasi enum */
$allowed = ['menunggu','disetujui','ditolak'];
if (!in_array($status, $allowed)) {
    exit("Status tidak valid");
}

mysqli_query($koneksi, "
    UPDATE data_pinjam
    SET status = '$status'
    WHERE id_data = $id
");

/* Kembali ke dashboard */
header("Location: index.php");
exit;
