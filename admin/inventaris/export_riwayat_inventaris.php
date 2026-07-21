<?php
require_once __DIR__ . '/../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: ../../index.php');
  exit;
}

$id_periode = $_GET['id_periode'] ?? null;
if (!$id_periode) { header('Location: ../lab/data_lab.php'); exit; }
$id_periode = mysqli_real_escape_string($koneksi, $id_periode);

$periodeRes = mysqli_query($koneksi, "SELECT p.*, l.nama_lab FROM periode_inventaris p
    JOIN data_lab l ON l.id_lab = p.id_lab
    WHERE p.id_periode = '$id_periode'");
$periode = mysqli_fetch_assoc($periodeRes);
if (!$periode) { header('Location: ../lab/data_lab.php'); exit; }

$namaBulan = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
    7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];

$acQuery   = mysqli_query($koneksi, "SELECT * FROM riwayat_ac WHERE id_periode = '$id_periode' ORDER BY nomor_ac ASC");
$mejaQuery = mysqli_query($koneksi, "SELECT * FROM riwayat_meja WHERE id_periode = '$id_periode' ORDER BY nomor_meja ASC");

function labelKondisi($val) {
    return match ($val) {
        'normal'       => 'Normal',
        'rusak'        => 'Rusak',
        'instal_ulang' => 'Instal Ulang',
        'tidak_ada'    => 'Tidak Ada',
        default        => ucfirst((string) $val),
    };
}

$labelPeriode = $namaBulan[(int)$periode['bulan']] . ' ' . $periode['tahun'];
$namaFile = 'Riwayat_Inventaris_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $periode['nama_lab']) . '_' . preg_replace('/\s+/', '_', $labelPeriode);

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $namaFile . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF";
?>
<html>
<head><meta charset="UTF-8"></head>
<body>

<table border="1">
    <tr><td colspan="6" style="font-size:16px;font-weight:bold;">Riwayat Inventaris Bulanan</td></tr>
    <tr><td><b>Nama Lab</b></td><td colspan="5"><?= htmlspecialchars($periode['nama_lab']); ?></td></tr>
    <tr><td><b>Periode</b></td><td colspan="5"><?= htmlspecialchars($labelPeriode); ?></td></tr>
    <tr><td><b>Jumlah Kursi</b></td><td colspan="5"><?= (int) $periode['jumlah_kursi']; ?></td></tr>
    <tr><td><b>Jumlah Meja</b></td><td colspan="5"><?= (int) $periode['jumlah_meja']; ?></td></tr>
    <tr><td><b>Jumlah AC</b></td><td colspan="5"><?= (int) $periode['jumlah_ac']; ?></td></tr>
    <tr><td><b>Dicatat Oleh</b></td><td colspan="5"><?= htmlspecialchars($periode['dicatat_oleh'] ?? '-'); ?></td></tr>
    <tr><td><b>Tanggal Dicatat</b></td><td colspan="5"><?= date('d-m-Y H:i', strtotime($periode['tanggal_catat'])); ?></td></tr>
    <tr><td><b>Keterangan</b></td><td colspan="5"><?= htmlspecialchars($periode['keterangan'] ?? '-'); ?></td></tr>
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