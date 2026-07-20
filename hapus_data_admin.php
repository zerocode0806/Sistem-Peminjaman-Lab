<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: data_admin.php');
    exit;
}

// Jangan biarkan admin menghapus akunnya sendiri
$currentAdminId = $_SESSION['user']['id_admin'] ?? null;
if ($currentAdminId !== null && (int)$currentAdminId === $id) {
    header('Location: data_admin.php?error=self');
    exit;
}

$stmt = mysqli_prepare($koneksi, "DELETE FROM admin WHERE id_admin = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

header('Location: data_admin.php');
exit;