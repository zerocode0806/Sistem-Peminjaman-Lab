<?php
session_start();
include 'koneksi.php';

/* ===============================
   CEK LOGIN MAHASISWA
================================ */
if (!isset($_SESSION['mahasiswa']['nim'])) {
    header('Location: login_mhs.php');
    exit;
}

$nim = $_SESSION['mahasiswa']['nim'];

/* ===============================
   AMBIL DATA MAHASISWA
================================ */
$mhsQuery = mysqli_query($koneksi, "
    SELECT nama, no_telepon, alamat
    FROM mahasiswa
    WHERE nim = '$nim'
");

$dataMhs = mysqli_fetch_assoc($mhsQuery);
if (!$dataMhs) {
    die("Data mahasiswa tidak ditemukan.");
}

/* ===============================
   AMBIL LAB TERSEDIA
================================ */
$labQuery = mysqli_query($koneksi, "
    SELECT nama_lab
    FROM data_lab
    WHERE status = 'availabel'
    ORDER BY nama_lab ASC
");

/* ===============================
   PROSES SIMPAN PINJAM
================================ */
if (isset($_POST['simpan'])) {

    $nama_lab    = mysqli_real_escape_string($koneksi, $_POST['nama_lab']);
    $tanggal     = $_POST['tanggal'];
    $jam_mulai   = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $status      = 'menunggu';

    if ($jam_selesai <= $jam_mulai) {
        $error = "Jam selesai harus lebih besar dari jam mulai";
    } else {
        // Mulai transaksi
        mysqli_begin_transaction($koneksi);

        try {

            // 🔎 Cek stok lab
            $cekStok = mysqli_query($koneksi, "
                SELECT stok 
                FROM data_lab 
                WHERE nama_lab = '$nama_lab' 
                FOR UPDATE
            ");

            $dataLab = mysqli_fetch_assoc($cekStok);

            if (!$dataLab || $dataLab['stok'] <= 0) {
                throw new Exception("Stok laboratorium sudah habis.");
            }

            // ✅ Insert data peminjaman
            $insert = mysqli_query($koneksi, "
            INSERT INTO data_pinjam
            (nim, nama_lab, tanggal, jam_mulai, jam_selesai, status)
            VALUES
            ('$nim', '$nama_lab', '$tanggal', '$jam_mulai', '$jam_selesai', '$status')
            ");

            if (!$insert) {
                throw new Exception("Gagal menyimpan data peminjaman.");
            }

            // ➖ Kurangi stok
            $updateStok = mysqli_query($koneksi, "
                UPDATE data_lab 
                SET stok = stok - 1 
                WHERE nama_lab = '$nama_lab'
            ");

            if (!$updateStok) {
                throw new Exception("Gagal mengurangi stok.");
            }

            // Commit jika semua berhasil
            mysqli_commit($koneksi);

            header("Location: dashboard_mhs.php?msg=success");
            exit;

        } catch (Exception $e) {

            mysqli_rollback($koneksi);
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ajukan Peminjaman Laboratorium</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background-color: #f5f6fa;
}
</style>
</head>

<body>

<div class="container py-4">

    <!-- Header -->
    <div class="mb-4">
        <h3 class="fw-bold">
            <i class="bi bi-calendar-plus"></i> Ajukan Peminjaman Laboratorium
        </h3>
        <small class="text-muted">
            Silakan periksa data Anda sebelum mengajukan peminjaman
        </small>
    </div>

    <!-- Card Form -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="row g-3">

                <!-- DATA MAHASISWA -->
                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input type="text" class="form-control"
                           value="<?= htmlspecialchars($dataMhs['nama']) ?>" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">NIM</label>
                    <input type="text" class="form-control"
                           value="<?= htmlspecialchars($nim) ?>" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" class="form-control"
                           value="<?= htmlspecialchars($dataMhs['no_telepon']) ?>" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Alamat</label>
                    <input type="text" class="form-control"
                           value="<?= htmlspecialchars($dataMhs['alamat']) ?>" readonly>
                </div>

                <hr class="my-3">

                <!-- DATA PINJAM -->
                <div class="col-md-6">
                    <label class="form-label">Laboratorium</label>
                    <select name="nama_lab" class="form-select" required>
                        <option value="">-- Pilih Laboratorium --</option>
                        <?php while ($lab = mysqli_fetch_assoc($labQuery)): ?>
                            <option value="<?= htmlspecialchars($lab['nama_lab']) ?>">
                                <?= htmlspecialchars($lab['nama_lab']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Jam Mulai</label>
                    <input type="time" name="jam_mulai" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Jam Selesai</label>
                    <input type="time" name="jam_selesai" class="form-control" required>
                </div>

                <!-- ACTION -->
                <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                    <a href="dashboard_mhs.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" name="simpan" class="btn btn-primary px-4">
                        <i class="bi bi-send"></i> Ajukan
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
