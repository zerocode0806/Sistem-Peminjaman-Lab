<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

/* Ambil lab yang tersedia */
$labQuery = mysqli_query($koneksi, "
    SELECT id_lab, nama_lab
    FROM data_lab
    WHERE status = 'availabel'
    ORDER BY nama_lab ASC
");

/* Simpan data */
/* Simpan data */
if (isset($_POST['simpan'])) {

    $nim            = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $nama_mahasiswa = mysqli_real_escape_string($koneksi, $_POST['nama_mahasiswa']);
    $no_telp        = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $alamat         = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $nama_lab       = mysqli_real_escape_string($koneksi, $_POST['nama_lab']);
    $tanggal        = $_POST['tanggal'];
    $jam_mulai      = $_POST['jam_mulai'];
    $jam_selesai    = $_POST['jam_selesai'];
    $status         = 'menunggu';

    if ($jam_selesai <= $jam_mulai) {
        $error = "Jam selesai harus lebih besar dari jam mulai.";
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
                (nim, nama_mahasiswa, no_telp, alamat, nama_lab, tanggal, jam_mulai, jam_selesai, status)
                VALUES
                ('$nim', '$nama_mahasiswa', '$no_telp', '$alamat', '$nama_lab', '$tanggal', '$jam_mulai', '$jam_selesai', '$status')
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

            header("Location: dashboard.php?msg=success");
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
<title>Tambah Data Peminjaman Lab</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background-color: #f5f6fa;
}
.form-container {
    max-width: 900px;
    margin: 40px auto;
}
</style>
</head>

<body>

<div class="container form-container">

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <h4 class="fw-bold mb-4">
                <i class="bi bi-calendar-plus"></i> Tambah Data Peminjaman Laboratorium
            </h4>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">

                <!-- Data Mahasiswa -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">NIM</label>
                        <input type="text" name="nim" class="form-control" required>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label">Nama Mahasiswa</label>
                        <input type="text" name="nama_mahasiswa" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="no_telp" class="form-control" required>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2" required></textarea>
                    </div>
                </div>

                <hr>

                <!-- Data Peminjaman -->
                <div class="mb-3">
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

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" name="jam_mulai" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jam Selesai</label>
                        <input type="time" name="jam_selesai" class="form-control" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>

                    <button type="submit" name="simpan" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Data
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
