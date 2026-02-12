<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['mahasiswa'])) {
    header('Location: login_mhs.php');
    exit;
}

$nama_mhs = $_SESSION['mahasiswa']['nama'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Mahasiswa – Peminjaman Lab</title>

<!-- Bootstrap -->
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

<!-- Topbar Mobile -->
<nav class="navbar navbar-light bg-white shadow-sm d-md-none">
    <div class="container-fluid">
        <button class="btn btn-outline-primary" id="toggleSidebar">
            <i class="bi bi-list"></i>
        </button>
        <span class="fw-bold"><?= htmlspecialchars($nama_mhs) ?></span>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar position-fixed p-3" id="sidebar">
    <h6 class="text-white text-center mb-4 d-none d-md-block">
        <?= htmlspecialchars($nama_mhs) ?>
    </h6>

    <ul class="nav flex-column gap-1">
        <li class="nav-item">
            <a class="nav-link active" href="dashboard_mhs.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>


        <li class="nav-item">
            <a class="nav-link" href="riwayat_pinjam_mhs.php">
                <i class="bi bi-clock-history me-2"></i> Riwayat Saya
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-danger" href="logout_mhs.php"
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
    <h3 class="fw-bold mb-1">Dashboard Peminjaman Lab</h3>
    <small class="text-muted">
        Selamat datang, <?= htmlspecialchars($nama_mhs) ?>
    </small>

   
</div>

<!-- Cards -->
<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Total Pengajuan</small>
                <h3 class="fw-bold mt-1" id="total_pinjam">0</h3>
            </div>
        </div>
    </div>

    <!-- Menunggu -->
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Menunggu</small>
                <h3 class="fw-bold mt-1" id="total_menunggu">0</h3>
            </div>
        </div>
    </div>

    <!-- Disetujui -->
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Disetujui</small>
                <h3 class="fw-bold mt-1" id="total_disetujui">0</h3>
            </div>
        </div>
    </div>

    <!-- Ditolak -->
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Ditolak</small>
                <h3 class="fw-bold mt-1" id="total_ditolak">0</h3>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">

        <!-- Header Card -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-semibold mb-0">
                Peminjaman Saya
            </h6>

            <a href="tambah_data_pinjam_mhs.php" class="btn btn-sm btn-primary">
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
                        <th>Laboratorium</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Memuat data...
                        </td>
                    </tr>
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
function loadDashboard() {
    fetch('ajax_dashboard_mhs.php')
        .then(response => response.json())
        .then(data => {

            if (data.error) {
                alert("Session habis, silakan login kembali.");
                window.location.href = 'login_mhs.php';
                return;
            }

            // Update Card
            document.getElementById('total_pinjam').textContent = data.total_pinjam;
            document.getElementById('total_menunggu').textContent = data.total_menunggu;
            document.getElementById('total_disetujui').textContent = data.total_disetujui;
            document.getElementById('total_ditolak').textContent = data.total_ditolak;

            // Update Table
            let tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';

            if (data.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Belum ada pengajuan peminjaman
                        </td>
                    </tr>
                `;
                return;
            }

            data.data.forEach(row => {

                let statusClass = {
                    'menunggu': 'bg-warning text-dark',
                    'disetujui': 'bg-success',
                    'ditolak': 'bg-danger'
                }[row.status] ?? 'bg-secondary';

                tbody.innerHTML += `
                    <tr>
                        <td>${row.nama_lab}</td>
                        <td>${new Date(row.tanggal).toLocaleDateString('id-ID')}</td>
                        <td>${row.jam_mulai.substring(0,5)} – ${row.jam_selesai.substring(0,5)}</td>
                        <td>
                            <span class="badge ${statusClass}">
                                ${row.status.charAt(0).toUpperCase() + row.status.slice(1)}
                            </span>
                        </td>
                    </tr>
                `;
            });

        })
        .catch(error => console.error(error));
}

// Load pertama kali
loadDashboard();

// Auto refresh tiap 10 detik (opsional)
setInterval(loadDashboard, 10000);
</script>


</body>
</html>
