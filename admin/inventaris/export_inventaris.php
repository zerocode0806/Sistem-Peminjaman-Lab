<?php
require_once __DIR__ . '/../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: ../../index.php');
  exit;
}

$id_lab = $_GET['id_lab'] ?? null;
if (!$id_lab) { header('Location: ../lab/data_lab.php'); exit; }
$id_lab = mysqli_real_escape_string($koneksi, $id_lab);

$labRes = mysqli_query($koneksi, "SELECT * FROM data_lab WHERE id_lab = '$id_lab'");
$lab = mysqli_fetch_assoc($labRes);
if (!$lab) { header('Location: ../lab/data_lab.php'); exit; }

$acQuery   = mysqli_query($koneksi, "SELECT * FROM inventaris_ac WHERE id_lab = '$id_lab' ORDER BY nomor_ac ASC");
$mejaQuery = mysqli_query($koneksi, "SELECT * FROM inventaris_meja WHERE id_lab = '$id_lab' ORDER BY nomor_meja ASC");

function labelKondisi($val) {
    return match ($val) {
        'normal'       => 'Normal',
        'rusak'        => 'Rusak',
        'instal_ulang' => 'Instal Ulang',
        'tidak_ada'    => 'Tidak Ada',
        default        => ucfirst((string) $val),
    };
}

$namaLab   = $lab['nama_lab'];
$namaFile  = 'Inventaris_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $namaLab) . '_' . date('Ymd');

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $namaFile . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF"; // BOM agar karakter UTF-8 tampil benar di Excel
?>
<html>
<head><meta charset="UTF-8"></head>
<body>

<table border="1">
    <tr><td colspan="6" style="font-size:16px;font-weight:bold;">Data Inventaris Laboratorium</td></tr>
    <tr><td><b>Nama Lab</b></td><td colspan="5"><?= htmlspecialchars($namaLab); ?></td></tr>
    <tr><td><b>ID Lab</b></td><td colspan="5"><?= htmlspecialchars($lab['id_lab']); ?></td></tr>
    <tr><td><b>Jumlah Kursi</b></td><td colspan="5"><?= (int) ($lab['jumlah_kursi'] ?? 0); ?></td></tr>
    <tr><td><b>Jumlah Meja</b></td><td colspan="5"><?= (int) ($lab['jumlah_meja'] ?? 0); ?></td></tr>
    <tr><td><b>Tanggal Export</b></td><td colspan="5"><?= date('d-m-Y H:i'); ?></td></tr>
</table>

<br>

<table border="1">
    <tr style="background:#f0f0f0;font-weight:bold;">
        <td colspan="3">INVENTARIS AC</td>
    </tr>
    <tr style="background:#f0f0f0;font-weight:bold;">
        <td>No</td>
        <td>Unit AC</td>
        <td>Kondisi</td>
    </tr>
    <?php
    $no = 1;
    if (mysqli_num_rows($acQuery) > 0):
        while ($ac = mysqli_fetch_assoc($acQuery)):
    ?>
    <tr>
        <td><?= $no++; ?></td>
        <td>AC Unit #<?= (int) $ac['nomor_ac']; ?></td>
        <td><?= labelKondisi($ac['kondisi']); ?></td>
    </tr>
    <?php endwhile; else: ?>
    <tr><td colspan="3">Belum ada data AC</td></tr>
    <?php endif; ?>
</table>

<br>

<table border="1">
    <tr style="background:#f0f0f0;font-weight:bold;">
        <td colspan="6">INVENTARIS MEJA &amp; PERANGKAT</td>
    </tr>
    <tr style="background:#f0f0f0;font-weight:bold;">
        <td>No Meja</td>
        <td>CPU</td>
        <td>Keyboard</td>
        <td>Mouse</td>
        <td>Monitor</td>
        <td>Kursi</td>
    </tr>
    <?php while ($m = mysqli_fetch_assoc($mejaQuery)): ?>
    <tr>
        <td><?= (int) $m['nomor_meja']; ?></td>
        <td><?= labelKondisi($m['cpu_kondisi']); ?></td>
        <td><?= labelKondisi($m['keyboard_kondisi']); ?></td>
        <td><?= labelKondisi($m['mouse_kondisi']); ?></td>
        <td><?= labelKondisi($m['monitor_kondisi']); ?></td>
        <td><?= labelKondisi($m['kursi_kondisi']); ?></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>