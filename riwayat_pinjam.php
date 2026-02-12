<?php
session_start();
include 'koneksi.php';

/* ===============================
   CEK LOGIN ADMIN
================================ */
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

/* ===============================
   AMBIL DATA RIWAYAT PINJAM
================================ */
$query = mysqli_query($koneksi, "
    SELECT *
    FROM data_pinjam
    WHERE status IN ('disetujui')
    ORDER BY tanggal DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Riwayat Peminjaman – Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background-color: #f5f6fa;
}

/* Sidebar */
.sidebar {
    width: 240px;
    min-height: 100vh;
    background: #1f2937;
    transition: all .3s;
    z-index: 1050; /* PENTING */
}

.sidebar a {
    color: #cbd5e1;
    text-decoration: none;
}

.sidebar a:hover,
.sidebar .active {
    background: #2563eb;
    color: #fff;
}

.sidebar .nav-link {
    border-radius: 6px;
}

.content {
    margin-left: 240px;
}

@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        left: -240px;
    }
    .sidebar.show {
        left: 0;
    }
    .content {
        margin-left: 0;
    }
}
</style>
</head>

<body>

<!-- Topbar Mobile -->
<nav class="navbar navbar-light bg-white shadow-sm d-md-none">
    <div class="container-fluid">
        <button class="btn btn-outline-primary" id="toggleSidebar">
            <i class="bi bi-list"></i>
        </button>
        <span class="fw-bold"><?= $_SESSION['user']['nama'] ?></span>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar position-fixed p-3" id="sidebar">
    <h5 class="text-white text-center mb-4 d-none d-md-block"><?= $_SESSION['user']['nama'] ?></h5>

    <ul class="nav flex-column gap-1">
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="data_lab.php">
                <i class="bi bi-building me-2"></i> Data Laboratorium
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="data_mhs.php">
                <i class="bi bi-people me-2"></i> Data Mahasiswa
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="riwayat_pinjam.php">
                <i class="bi bi-clock-history me-2"></i> Riwayat Peminjaman
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="arsip_peminjaman.php">
                <i class="bi bi-people me-2"></i> Arsip Peminjaman
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="logout.php"
               onclick="return confirm('Yakin ingin logout?')">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </li>
    </ul>
</div>

<!-- Content -->
<div class="content p-4">
<div class="container-fluid px-0 px-md-4">

    <!-- Header -->
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Riwayat Peminjaman Lab</h3>
        <small class="text-muted">
            Daftar peminjaman yang sudah diproses
        </small>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>NIM</th>
                            <th>Laboratorium</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($query) == 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Belum ada riwayat peminjaman
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nim']) ?></td>
                            <td><?= htmlspecialchars($row['nama_lab']) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td>
                                <?= substr($row['jam_mulai'], 0, 5) ?> –
                                <?= substr($row['jam_selesai'], 0, 5) ?>
                            </td>
                            <td>
                                <?php
                                    $badge = match($row['status']) {
                                        'disetujui' => 'bg-success',
                                        'ditolak'   => 'bg-danger',
                                        default     => 'bg-secondary'
                                    };
                                ?>
                                <span class="badge <?= $badge ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="checkout_pinjam.php?id=<?= $row['id_data'] ?>"
                                   class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-info-circle"></i>
                                    <span class="d-none d-md-inline"> Detail</span>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('toggleSidebar')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
});
</script>

</body>
</html>
