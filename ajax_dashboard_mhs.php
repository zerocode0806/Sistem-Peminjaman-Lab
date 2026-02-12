<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

/* ===============================
   CEK LOGIN
================================ */
if (!isset($_SESSION['mahasiswa'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$nim_mhs = $_SESSION['mahasiswa']['nim'];

/* ===============================
   TOTAL DATA
================================ */

// Total Pengajuan
$qPengajuan = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total 
    FROM data_pinjam 
    WHERE nim = '$nim_mhs'
");
$total_pinjam = mysqli_fetch_assoc($qPengajuan)['total'] ?? 0;

// Menunggu
$qMenunggu = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total 
    FROM data_pinjam 
    WHERE status = 'menunggu' AND nim = '$nim_mhs'
");
$total_menunggu = mysqli_fetch_assoc($qMenunggu)['total'] ?? 0;

// Disetujui
$qSetuju = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total 
    FROM data_pinjam 
    WHERE status = 'disetujui' AND nim = '$nim_mhs'
");
$total_disetujui = mysqli_fetch_assoc($qSetuju)['total'] ?? 0;

// Ditolak
$qTolak = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total 
    FROM data_pinjam 
    WHERE status = 'ditolak' AND nim = '$nim_mhs'
");
$total_ditolak = mysqli_fetch_assoc($qTolak)['total'] ?? 0;

/* ===============================
   DATA TABLE (MENUNGGU)
================================ */
$query = mysqli_query($koneksi, "
    SELECT *
    FROM data_pinjam
    WHERE nim = '$nim_mhs'
      AND status = 'menunggu'
    ORDER BY tanggal DESC
");

$data = [];
while ($row = mysqli_fetch_assoc($query)) {
    $data[] = $row;
}

/* ===============================
   RETURN JSON
================================ */
echo json_encode([
    'total_pinjam'     => $total_pinjam,
    'total_menunggu'   => $total_menunggu,
    'total_disetujui'  => $total_disetujui,
    'total_ditolak'    => $total_ditolak,
    'data'             => $data
]);
