<?php
session_start();
include 'koneksi.php';

/* ===============================
   CEK LOGIN ADMIN
================================ */
if (!isset($_SESSION['mahasiswa'])) {
    header('Location: index.php');
    exit;
}

/* ===============================
   VALIDASI ID PINJAM
================================ */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

/* ===============================
   AMBIL DATA PINJAM + MAHASISWA
================================ */
$query = mysqli_query($koneksi, "
    SELECT 
        p.id_data,
        p.nim,
        p.nama_lab,
        p.tanggal,
        p.jam_mulai,
        p.jam_selesai,
        p.status,

        m.nama AS nama_mhs,
        m.no_telepon,
        m.alamat
    FROM data_pinjam p
    JOIN mahasiswa m ON p.nim = m.nim
    WHERE p.id_data = '$id'
    LIMIT 1
");

$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); window.location='dashboard.php';</script>";
    exit;
}

/* ===============================
   UPDATE STATUS
================================ */
if (isset($_POST['update_status'])) {
    $status = $_POST['status'];

    if (in_array($status, ['menunggu', 'disetujui', 'ditolak'])) {
        mysqli_query($koneksi, "
            UPDATE data_pinjam
            SET status = '$status'
            WHERE id_data = '$id'
        ");

        header("Location: dashboard.php?msg=success");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Detail Peminjaman</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background: #f5f6fa;
}
.detail-label {
    font-size: .85rem;
    color: #6b7280;
}
.detail-value {
    font-weight: 600;
}
</style>
</head>

<body>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">
            <i class="bi bi-info-circle"></i> Detail Peminjaman
        </h4>
        <a href="riwayat_pinjam_mhs.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">Status berhasil diperbarui</div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- DATA MAHASISWA -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Data Mahasiswa</h6>

                    <div class="mb-2">
                        <div class="detail-label">Nama</div>
                        <div class="detail-value"><?= htmlspecialchars($data['nama_mhs']) ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">NIM</div>
                        <div class="detail-value"><?= htmlspecialchars($data['nim']) ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">No. Telepon</div>
                        <div class="detail-value"><?= htmlspecialchars($data['no_telepon']) ?></div>
                    </div>

                    <div>
                        <div class="detail-label">Alamat</div>
                        <div class="detail-value"><?= htmlspecialchars($data['alamat']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DATA PEMINJAMAN -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Data Peminjaman</h6>

                    <div class="mb-2">
                        <div class="detail-label">Laboratorium</div>
                        <div class="detail-value"><?= htmlspecialchars($data['nama_lab']) ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Tanggal</div>
                        <div class="detail-value">
                            <?= date('d/m/Y', strtotime($data['tanggal'])) ?>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Waktu</div>
                        <div class="detail-value">
                            <?= substr($data['jam_mulai'], 0, 5) ?> –
                            <?= substr($data['jam_selesai'], 0, 5) ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="detail-label">Status</div>
                        <?php
                        $badge = match($data['status']) {
                            'menunggu'  => 'bg-warning text-dark',
                            'disetujui' => 'bg-success',
                            'ditolak'   => 'bg-danger',
                            default     => 'bg-secondary'
                        };
                        ?>
                        <span class="badge <?= $badge ?>">
                            <?= ucfirst($data['status']) ?>
                        </span>
                    </div>

                    <!-- FORM UPDATE STATUS -->
                    <!-- <form method="POST" class="d-flex gap-2">
                        <select name="status" class="form-select" required>
                            <option value="menunggu" <?= $data['status']=='menunggu'?'selected':'' ?>>
                                Menunggu
                            </option>
                            <option value="disetujui" <?= $data['status']=='disetujui'?'selected':'' ?>>
                                Disetujui
                            </option>
                            <option value="ditolak" <?= $data['status']=='ditolak'?'selected':'' ?>>
                                Ditolak
                            </option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-primary">
                            Simpan
                        </button>
                    </form> -->

                </div>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
