<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

/* Hanya bisa diakses oleh mahasiswa yang sudah login */
if (!isset($_SESSION['mahasiswa']['nim'])) {
    echo json_encode(['booked' => []]);
    exit;
}

$nama_lab    = mysqli_real_escape_string($koneksi, $_GET['nama_lab']    ?? '');
$tanggal     = mysqli_real_escape_string($koneksi, $_GET['tanggal']     ?? '');
$jam_mulai   = mysqli_real_escape_string($koneksi, $_GET['jam_mulai']   ?? '');
$jam_selesai = mysqli_real_escape_string($koneksi, $_GET['jam_selesai'] ?? '');

$booked = [];

if ($nama_lab !== '' && $tanggal !== '' && $jam_mulai !== '' && $jam_selesai !== '') {

    /* Ambil semua kursi yang jadwalnya beririsan dengan rentang jam yang diminta,
       untuk peminjaman yang masih berstatus menunggu / disetujui */
    $q = mysqli_query($koneksi, "
        SELECT kursi
        FROM data_pinjam
        WHERE nama_lab   = '$nama_lab'
          AND tanggal     = '$tanggal'
          AND status IN ('menunggu', 'disetujui')
          AND kursi IS NOT NULL
          AND jam_mulai   < '$jam_selesai'
          AND jam_selesai > '$jam_mulai'
    ");

    if ($q) {
        while ($row = mysqli_fetch_assoc($q)) {
            $booked[] = (int) $row['kursi'];
        }
    }
}

echo json_encode(['booked' => $booked]);