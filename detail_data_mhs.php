<?php
session_start();
include 'koneksi.php';

if (empty($_GET['nim'])) {
    die("NIM tidak ditemukan di URL");
}

$nim = mysqli_real_escape_string($koneksi, trim($_GET['nim']));


/* ===============================
   DATA MAHASISWA
================================ */
$qMhs = mysqli_query($koneksi, "
    SELECT nama, no_telepon, alamat
    FROM mahasiswa
    WHERE nim = '$nim'
");
$mhs = mysqli_fetch_assoc($qMhs);

if (!$mhs) {
    die("Data mahasiswa tidak ditemukan");
}

/* ===============================
   RIWAYAT PINJAM
================================ */
$qPinjam = mysqli_query($koneksi, "
    SELECT *
    FROM data_pinjam
    WHERE nim = '$nim'
      AND status IN ('selesai', 'ditolak')
    ORDER BY tanggal DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Data Mahasiswa</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background-color: #f5f6fa;
}
</style>
</head>

<body>

<div class="container-fluid px-4 py-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">
            <i class="bi bi-person-circle me-2"></i> Detail Mahasiswa
        </h3>
        <a href="data_mhs.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- CARD DATA MAHASISWA -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-4">Informasi Mahasiswa</h5>
            <table class="table table-borderless mb-0">
                <tr>
                    <td width="150"><strong>Nama</strong></td>
                    <td>: <?= htmlspecialchars($mhs['nama']) ?></td>
                </tr>
                <tr>
                    <td><strong>NIM</strong></td>
                    <td>: <?= htmlspecialchars($nim) ?></td>
                </tr>
                <tr>
                    <td><strong>No. Telepon</strong></td>
                    <td>: <?= htmlspecialchars($mhs['no_telepon']) ?></td>
                </tr>
                <tr>
                    <td><strong>Alamat</strong></td>
                    <td>: <?= htmlspecialchars($mhs['alamat']) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- RIWAYAT PINJAM -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="mb-4">
                <i class="bi bi-clock-history me-2"></i>
                Riwayat Peminjaman Laboratorium
            </h5>

            <div class="row">
                <?php if (mysqli_num_rows($qPinjam) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($qPinjam)): ?>
                        <div class="col-12 col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-semibold">
                                            <?= htmlspecialchars($row['nama_lab']) ?>
                                        </span>
                                        <?php
                                        $badge = '';
                                        switch ($row['status']) {
                                            case 'selesai':
                                                $badge = 'bg-success';
                                                break;
                                            case 'ditolak':
                                                $badge = 'bg-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $badge ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </div>

                                    <div class="text-muted small mb-2">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <?= date('d/m/Y', strtotime($row['tanggal'])) ?>
                                    </div>

                                    <div class="small">
                                        <strong>Waktu:</strong><br>
                                        <?= substr($row['jam_mulai'], 0, 5) ?>
                                        –
                                        <?= substr($row['jam_selesai'], 0, 5) ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Belum ada riwayat peminjaman
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
