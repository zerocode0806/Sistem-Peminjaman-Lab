<?php
require_once __DIR__ . '/../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: ../../index.php');
  exit;
}

$id_lab      = $_POST['id_lab'] ?? null;
$bulan       = (int) ($_POST['bulan'] ?? date('n'));
$tahun       = (int) ($_POST['tahun'] ?? date('Y'));
$keterangan  = trim($_POST['keterangan'] ?? '');

if (!$id_lab || $bulan < 1 || $bulan > 12 || $tahun < 2000) {
    header('Location: ../lab/data_lab.php');
    exit;
}

$id_lab      = mysqli_real_escape_string($koneksi, $id_lab);
$keterangan  = mysqli_real_escape_string($koneksi, $keterangan);
$dicatatOleh = mysqli_real_escape_string($koneksi, $_SESSION['user']['nama'] ?? 'Admin');

// Hitung ringkasan saat ini
$labRes  = mysqli_query($koneksi, "SELECT * FROM data_lab WHERE id_lab = '$id_lab'");
$lab     = mysqli_fetch_assoc($labRes);
if (!$lab) { header('Location: ../lab/data_lab.php'); exit; }

$jumlahKursi = (int) ($lab['jumlah_kursi'] ?? 0);

$acQuery   = mysqli_query($koneksi, "SELECT * FROM inventaris_ac WHERE id_lab = '$id_lab' ORDER BY nomor_ac ASC");
$mejaQuery = mysqli_query($koneksi, "SELECT * FROM inventaris_meja WHERE id_lab = '$id_lab' ORDER BY nomor_meja ASC");

$acData   = [];
$mejaData = [];
while ($row = mysqli_fetch_assoc($acQuery))   { $acData[] = $row; }
while ($row = mysqli_fetch_assoc($mejaQuery)) { $mejaData[] = $row; }

$jumlahAc   = count($acData);
$jumlahMeja = count($mejaData);

// Cek apakah periode untuk lab+bulan+tahun ini sudah pernah dicatat
$cek = mysqli_query($koneksi, "SELECT id_periode FROM periode_inventaris
    WHERE id_lab = '$id_lab' AND bulan = '$bulan' AND tahun = '$tahun'");
$existing = mysqli_fetch_assoc($cek);

if ($existing) {
    // Sudah ada -> perbarui snapshot (timpa riwayat lama untuk periode yang sama)
    $id_periode = $existing['id_periode'];
    mysqli_query($koneksi, "DELETE FROM riwayat_ac WHERE id_periode = '$id_periode'");
    mysqli_query($koneksi, "DELETE FROM riwayat_meja WHERE id_periode = '$id_periode'");
    mysqli_query($koneksi, "UPDATE periode_inventaris SET
        jumlah_kursi = '$jumlahKursi',
        jumlah_meja  = '$jumlahMeja',
        jumlah_ac    = '$jumlahAc',
        tanggal_catat = NOW(),
        dicatat_oleh = '$dicatatOleh',
        keterangan   = '$keterangan'
        WHERE id_periode = '$id_periode'");
} else {
    mysqli_query($koneksi, "INSERT INTO periode_inventaris
        (id_lab, bulan, tahun, jumlah_kursi, jumlah_meja, jumlah_ac, dicatat_oleh, keterangan)
        VALUES ('$id_lab', '$bulan', '$tahun', '$jumlahKursi', '$jumlahMeja', '$jumlahAc', '$dicatatOleh', '$keterangan')");
    $id_periode = mysqli_insert_id($koneksi);
}

// Simpan snapshot AC
foreach ($acData as $ac) {
    $nomor   = (int) $ac['nomor_ac'];
    $kondisi = mysqli_real_escape_string($koneksi, $ac['kondisi']);
    mysqli_query($koneksi, "INSERT INTO riwayat_ac (id_periode, id_lab, nomor_ac, kondisi)
        VALUES ('$id_periode', '$id_lab', '$nomor', '$kondisi')");
}

// Simpan snapshot Meja
foreach ($mejaData as $m) {
    $nomor    = (int) $m['nomor_meja'];
    $cpu      = mysqli_real_escape_string($koneksi, $m['cpu_kondisi']);
    $keyboard = mysqli_real_escape_string($koneksi, $m['keyboard_kondisi']);
    $mouse    = mysqli_real_escape_string($koneksi, $m['mouse_kondisi']);
    $monitor  = mysqli_real_escape_string($koneksi, $m['monitor_kondisi']);
    $kursi    = mysqli_real_escape_string($koneksi, $m['kursi_kondisi']);
    mysqli_query($koneksi, "INSERT INTO riwayat_meja
        (id_periode, id_lab, nomor_meja, cpu_kondisi, keyboard_kondisi, mouse_kondisi, monitor_kondisi, kursi_kondisi)
        VALUES ('$id_periode', '$id_lab', '$nomor', '$cpu', '$keyboard', '$mouse', '$monitor', '$kursi')");
}

header('Location: riwayat_inventaris.php?id_lab=' . urlencode($id_lab) . '&tersimpan=1');
exit;