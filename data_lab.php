<?php
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

$query = mysqli_query($koneksi, "SELECT * FROM data_lab ORDER BY nama_lab ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Data Laboratorium</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Icons -->
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
    transition: .3s;
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

/* Content */
.content {
    margin-left: 240px;
    transition: .3s;
}

/* Mobile */
@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        left: -240px;
        z-index: 1050;
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
            <a class="nav-link active" href="data_lab.php">
                <i class="bi bi-building me-2"></i> Data Laboratorium
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="data_mhs.php">
                <i class="bi bi-people me-2"></i> Data Mahasiswa
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link " href="riwayat_pinjam.php">
                <i class="bi bi-clock-history me-2"></i> Riwayat Peminjaman
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="arsip_peminjaman.php">
                <i class="bi bi-people me-2"></i> Arsip Peminjaman
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="logout.php" onclick="return confirm('Yakin ingin logout?')">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </li>
    </ul>
</div>

<!-- Content -->
<div class="content p-4">
<div class="container-fluid px-0 px-md-4">

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-building"></i> Data Laboratorium
        </h3>
        <small class="text-muted">Manajemen daftar laboratorium</small>
    </div>
    <a href="?page=lab_tambah" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>
        <span class="d-none d-md-inline">Tambah Lab</span>
    </a>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="60">No</th>
                        <th>Nama Laboratorium</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                if (mysqli_num_rows($query) > 0):
                    while ($lab = mysqli_fetch_assoc($query)):

                        $statusClass = $lab['status'] === 'availabel'
                            ? 'bg-success'
                            : 'bg-danger';

                        $statusText = $lab['status'] === 'availabel'
                            ? 'Tersedia'
                            : 'Tidak Tersedia';
                ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td>
                            <h6 class="mb-0"><?= htmlspecialchars($lab['nama_lab']); ?></h6>
                            <small class="text-muted">ID: <?= $lab['id_lab']; ?></small>
                            <small class="text-muted">Kuota: <?= $lab['stok']; ?></small>
                        </td>
                        <td>
                            <span class="badge <?= $statusClass; ?>">
                                <?= $statusText; ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted">
                            Data laboratorium belum tersedia
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Sidebar Toggle -->
<script>
document.getElementById('toggleSidebar')?.addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('show');
});
</script>

</body>
</html>
