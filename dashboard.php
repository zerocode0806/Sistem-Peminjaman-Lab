<?php
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

$query = mysqli_query($koneksi, "
    SELECT * FROM data_pinjam WHERE status = 'menunggu'
    ORDER BY tanggal DESC
");

// Total Lab
$qLab = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM data_lab");
$total_lab = mysqli_fetch_assoc($qLab)['total'] ?? 0;

// Peminjaman Menunggu
$qMenunggu = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total 
    FROM data_pinjam 
    WHERE status = 'menunggu'
");
$total_menunggu = mysqli_fetch_assoc($qMenunggu)['total'] ?? 0;

// Peminjaman Disetujui
$qSetuju = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total 
    FROM data_pinjam 
    WHERE status = 'disetujui'
");
$total_disetujui = mysqli_fetch_assoc($qSetuju)['total'] ?? 0;

// Peminjaman Ditolak
$qTolak = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total 
    FROM data_pinjam 
    WHERE status = 'ditolak'
");
$total_ditolak = mysqli_fetch_assoc($qTolak)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Admin – Peminjaman Lab</title>

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
    transition: all .3s;
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
    transition: all .3s;
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

<!-- Topbar (Mobile) -->
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
            <a class="nav-link active" href="index.php">
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
             Dashboard Peminjaman Lab
        </h3>
        <small class="text-muted">Manajemen daftar laboratorium</small>
    </div>
    
</div>

<div class="row g-3 mb-4">

    <!-- Total Lab -->
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Total Lab</small>
                <h3 class="fw-bold mt-1"><?= $total_lab ?></h3>
            </div>
        </div>
    </div>

    <!-- Menunggu -->
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Menunggu</small>
                <h3 class="fw-bold mt-1"><?= $total_menunggu ?></h3>
            </div>
        </div>
    </div>

    <!-- Disetujui -->
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Disetujui</small>
                <h3 class="fw-bold mt-1"><?= $total_disetujui ?></h3>
            </div>
        </div>
    </div>

    <!-- Ditolak -->
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Ditolak</small>
                <h3 class="fw-bold mt-1"><?= $total_ditolak ?></h3>
            </div>
        </div>
    </div>

</div>


<div class="card border-0 shadow-sm">
    <div class="card-body">

        <!-- Header Card -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-semibold mb-0">
                Permintaan Peminjaman Terbaru
            </h6>

            <a href="tambah_data_pinjam.php" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i>
                <span class="d-none d-sm-inline ms-1">
                    Tambah Peminjaman
                </span>
            </a>

        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Laboratorium</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody id="dataTableBody">
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
                                $statusClass = match($row['status']) {
                                    'menunggu'  => 'bg-warning text-dark',
                                    'disetujui' => 'bg-success',
                                    'ditolak'   => 'bg-danger',
                                    default     => 'bg-secondary'
                                };
                            ?>
                            <span class="badge <?= $statusClass ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="approve_pinjam.php?id=<?= $row['id_data'] ?>"
                               class="btn btn-sm btn-outline-info">
                                <i class="bi bi-info-circle"></i>
                                <span class="d-none d-md-inline"> Detail</span>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
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

<script>
function loadData() {
    fetch('ajax_dashboard.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('dataTableBody').innerHTML = data;
        })
        .catch(error => console.error('Error:', error));
}

// Load pertama kali
loadData();

// Refresh setiap 5 detik
setInterval(loadData, 5000);
</script>


</body>
</html>

