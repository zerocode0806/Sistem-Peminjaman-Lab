<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['mahasiswa'])) {
    header('Location: login_mhs.php');
    exit;
}

$nama_mhs = $_SESSION['mahasiswa']['nama'];
$nim_mhs  = $_SESSION['mahasiswa']['nim'];

// Total Pengajuan
$qTotal = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM data_pinjam WHERE nim = '$nim_mhs'
");
$total_pinjam = mysqli_fetch_assoc($qTotal)['total'] ?? 0;

// Menunggu
$qMenunggu = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM data_pinjam WHERE nim = '$nim_mhs' AND status = 'menunggu'
");
$total_menunggu = mysqli_fetch_assoc($qMenunggu)['total'] ?? 0;

// Disetujui
$qSetuju = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM data_pinjam WHERE nim = '$nim_mhs' AND status = 'disetujui'
");
$total_disetujui = mysqli_fetch_assoc($qSetuju)['total'] ?? 0;

// Ditolak
$qTolak = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM data_pinjam WHERE nim = '$nim_mhs' AND status = 'ditolak'
");
$total_ditolak = mysqli_fetch_assoc($qTolak)['total'] ?? 0;

// Data peminjaman mahasiswa
$query = mysqli_query($koneksi, "
    SELECT * FROM data_pinjam WHERE nim = '$nim_mhs' ORDER BY tanggal DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Mahasiswa – Peminjaman Lab</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg:        #F7F7F5;
    --surface:   #FFFFFF;
    --border:    #E8E8E3;
    --text:      #18181B;
    --muted:     #8C8C8A;
    --accent:    #1A1A1A;
    --blue:      #2563EB;
    --blue-soft: #EFF4FF;
    --warn:      #D97706;
    --warn-soft: #FFFBEB;
    --green:     #16A34A;
    --green-soft:#F0FDF4;
    --red:       #DC2626;
    --red-soft:  #FEF2F2;
    --sidebar-w: 228px;
    --radius:    10px;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    font-size: 14px;
    line-height: 1.5;
}

/* ── SIDEBAR ── */
.sidebar {
    position: fixed;
    top: 0; left: 0; bottom: 0;
    width: var(--sidebar-w);
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    padding: 24px 16px;
    z-index: 1000;
    transition: transform .25s ease;
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 4px;
    margin-bottom: 32px;
}

.sidebar-logo .logo-icon {
    width: 32px; height: 32px;
    background: var(--accent);
    border-radius: 8px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.sidebar-logo .logo-icon i {
    color: #fff;
    font-size: 15px;
}

.sidebar-logo-text {
    line-height: 1.2;
}

.sidebar-logo-text strong {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.sidebar-logo-text span {
    font-size: 11px;
    color: var(--muted);
    font-weight: 400;
}

.nav-section {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--muted);
    padding: 0 8px;
    margin-bottom: 6px;
    margin-top: 16px;
}

.nav-section:first-of-type { margin-top: 0; }

.nav-item { list-style: none; }

.nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 7px;
    color: var(--muted);
    font-size: 13.5px;
    font-weight: 500;
    text-decoration: none;
    transition: background .15s, color .15s;
    margin-bottom: 1px;
}

.nav-link i { font-size: 15px; width: 18px; text-align: center; }

.nav-link:hover { background: var(--bg); color: var(--text); }
.nav-link.active { background: var(--accent); color: #fff; }
.nav-link.danger { color: #DC2626; }
.nav-link.danger:hover { background: var(--red-soft); color: var(--red); }

.sidebar-user {
    margin-top: auto;
    padding: 12px 10px;
    background: var(--bg);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-user .avatar {
    width: 32px; height: 32px;
    background: var(--accent);
    border-radius: 50%;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    font-size: 13px;
    font-weight: 600;
    color: #fff;
    font-family: 'DM Mono', monospace;
}

.sidebar-user-info strong {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
}

.sidebar-user-info span {
    font-size: 11px;
    color: var(--muted);
}

/* ── TOPBAR MOBILE ── */
.topbar {
    display: none;
    position: sticky;
    top: 0;
    z-index: 990;
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 12px 16px;
    align-items: center;
    justify-content: space-between;
}

.topbar-title { font-size: 14px; font-weight: 600; }

.btn-icon {
    width: 36px; height: 36px;
    border: 1px solid var(--border);
    background: var(--surface);
    border-radius: 8px;
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 16px;
    color: var(--text);
    transition: background .15s;
}

.btn-icon:hover { background: var(--bg); }

/* ── CONTENT ── */
.main {
    margin-left: var(--sidebar-w);
    min-height: 100vh;
    padding: 32px 36px;
}

.page-header {
    margin-bottom: 28px;
}

.page-header h1 {
    font-size: 20px;
    font-weight: 600;
    letter-spacing: -.3px;
    color: var(--text);
    margin-bottom: 2px;
}

.page-header p {
    font-size: 13px;
    color: var(--muted);
}

/* ── STAT CARDS ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 28px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    position: relative;
    overflow: hidden;
    transition: box-shadow .2s;
}

.stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.06); }

.stat-label {
    font-size: 11.5px;
    font-weight: 500;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.stat-label i { font-size: 13px; }

.stat-value {
    font-size: 28px;
    font-weight: 600;
    letter-spacing: -1px;
    color: var(--text);
    font-family: 'DM Mono', monospace;
    line-height: 1;
}

.stat-card .corner-dot {
    position: absolute;
    bottom: -10px; right: -10px;
    width: 48px; height: 48px;
    border-radius: 50%;
    opacity: .07;
}

.stat-card.card-blue   .corner-dot { background: var(--blue); }
.stat-card.card-warn   .corner-dot { background: var(--warn); }
.stat-card.card-green  .corner-dot { background: var(--green); }
.stat-card.card-red    .corner-dot { background: var(--red); }
.stat-card.card-blue   .stat-label { color: var(--blue); }
.stat-card.card-warn   .stat-label { color: var(--warn); }
.stat-card.card-green  .stat-label { color: var(--green); }
.stat-card.card-red    .stat-label { color: var(--red); }

/* ── TABLE CARD ── */
.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    border-bottom: 1px solid var(--border);
}

.card-header-left h2 {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 1px;
}

.card-header-left span {
    font-size: 12px;
    color: var(--muted);
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: var(--accent);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    border: none;
    border-radius: 7px;
    text-decoration: none;
    cursor: pointer;
    transition: opacity .15s;
    white-space: nowrap;
}

.btn-primary:hover { opacity: .85; color: #fff; }

/* ── TABLE ── */
.table-wrap { overflow-x: auto; }

table {
    width: 100%;
    border-collapse: collapse;
}

thead th {
    padding: 11px 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--muted);
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}

thead th:last-child { text-align: right; }

tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background .12s;
}

tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #FAFAF8; }

tbody td {
    padding: 13px 20px;
    font-size: 13.5px;
    color: var(--text);
    vertical-align: middle;
}

tbody td:last-child { text-align: right; }

.cell-label {
    font-size: 12px;
    color: var(--muted);
}

.nim-text {
    font-family: 'DM Mono', monospace;
    font-size: 12.5px;
    color: var(--text);
    font-weight: 500;
}

.time-range {
    font-family: 'DM Mono', monospace;
    font-size: 12px;
    color: var(--muted);
    background: var(--bg);
    padding: 3px 7px;
    border-radius: 5px;
    display: inline-block;
}

/* STATUS BADGE */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 9px;
    border-radius: 100px;
    font-size: 11.5px;
    font-weight: 500;
    white-space: nowrap;
}

.badge::before {
    content: '';
    width: 5px; height: 5px;
    border-radius: 50%;
    flex-shrink: 0;
}

.badge-menunggu  { background: var(--warn-soft);  color: var(--warn);  }
.badge-menunggu::before  { background: var(--warn); }
.badge-disetujui { background: var(--green-soft); color: var(--green); }
.badge-disetujui::before { background: var(--green); }
.badge-ditolak   { background: var(--red-soft);   color: var(--red);   }
.badge-ditolak::before   { background: var(--red); }
.badge-default   { background: var(--bg); color: var(--muted); }
.badge-default::before   { background: var(--muted); }

/* DETAIL BTN */
.btn-detail {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 12.5px;
    font-weight: 500;
    border-radius: 6px;
    text-decoration: none;
    transition: background .15s, border-color .15s;
}

.btn-detail:hover {
    background: var(--bg);
    border-color: #ccc;
    color: var(--text);
}

/* EMPTY STATE */
.empty-state {
    text-align: center;
    padding: 52px 20px;
    color: var(--muted);
}

.empty-state i { font-size: 32px; margin-bottom: 10px; display: block; }
.empty-state p { font-size: 13px; }

/* OVERLAY */
.sidebar-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.3);
    z-index: 999;
}

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.show { transform: translateX(0); }
    .sidebar-overlay.show { display: block; }
    .topbar { display: flex; }
    .main { margin-left: 0; padding: 16px; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    thead th:nth-child(3),
    thead th:nth-child(4),
    tbody td:nth-child(3),
    tbody td:nth-child(4) { display: none; }
}

@media (max-width: 480px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
</head>
<body>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="overlay"></div>

<!-- Mobile Topbar -->
<header class="topbar">
    <button class="btn-icon" id="toggleSidebar">
        <i class="bi bi-list"></i>
    </button>
    <span class="topbar-title">Dashboard</span>
    <div style="width:36px"></div>
</header>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon"><i class="bi bi-boxes"></i></div>
        <div class="sidebar-logo-text">
            <strong>LabSystem</strong>
            <span>Mahasiswa</span>
        </div>
    </div>

    <p class="nav-section">Menu</p>
    <ul style="list-style:none;padding:0;margin:0">
        <li class="nav-item">
            <a class="nav-link active" href="dashboard_mhs.php">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
        </li>
    </ul>

    <p class="nav-section">Peminjaman</p>
    <ul style="list-style:none;padding:0;margin:0">
        <li class="nav-item">
            <a class="nav-link" href="riwayat_pinjam_mhs.php">
                <i class="bi bi-clock-history"></i> Riwayat Saya
            </a>
        </li>
    </ul>

    <div style="flex:1"></div>

    <ul style="list-style:none;padding:0;margin:0 0 12px">
        <li class="nav-item">
            <a class="nav-link danger" href="logout_mhs.php" onclick="return confirm('Yakin ingin logout?')">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </li>
    </ul>

    <div class="sidebar-user">
        <div class="avatar"><?= strtoupper(substr($nama_mhs, 0, 1)) ?></div>
        <div class="sidebar-user-info">
            <strong><?= htmlspecialchars($nama_mhs) ?></strong>
            <span>Mahasiswa</span>
        </div>
    </div>
</aside>

<!-- Main Content -->
<main class="main">

    <div class="page-header">
        <h1>Dashboard</h1>
        <p>Selamat datang, <?= htmlspecialchars($nama_mhs) ?> — ringkasan peminjaman laboratorium Anda</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card card-blue">
            <div class="stat-label"><i class="bi bi-folder"></i> Total Pengajuan</div>
            <div class="stat-value" id="total_pinjam"><?= $total_pinjam ?></div>
            <div class="corner-dot"></div>
        </div>
        <div class="stat-card card-warn">
            <div class="stat-label"><i class="bi bi-hourglass-split"></i> Menunggu</div>
            <div class="stat-value" id="total_menunggu"><?= $total_menunggu ?></div>
            <div class="corner-dot"></div>
        </div>
        <div class="stat-card card-green">
            <div class="stat-label"><i class="bi bi-check-circle"></i> Disetujui</div>
            <div class="stat-value" id="total_disetujui"><?= $total_disetujui ?></div>
            <div class="corner-dot"></div>
        </div>
        <div class="stat-card card-red">
            <div class="stat-label"><i class="bi bi-x-circle"></i> Ditolak</div>
            <div class="stat-value" id="total_ditolak"><?= $total_ditolak ?></div>
            <div class="corner-dot"></div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <h2>Peminjaman Saya</h2>
                <span>Daftar pengajuan peminjaman laboratorium</span>
            </div>
            <a href="tambah_data_pinjam_mhs.php" class="btn-primary">
                <i class="bi bi-plus"></i>
                <span>Tambah Peminjaman</span>
            </a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Laboratorium</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                <?php
                $hasRow = false;
                while ($row = mysqli_fetch_assoc($query)):
                    $hasRow = true;
                    $badgeClass = match($row['status']) {
                        'menunggu'  => 'badge-menunggu',
                        'disetujui' => 'badge-disetujui',
                        'ditolak'   => 'badge-ditolak',
                        default     => 'badge-default'
                    };
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama_lab']) ?></td>
                        <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                        <td>
                            <span class="time-range">
                                <?= substr($row['jam_mulai'], 0, 5) ?> – <?= substr($row['jam_selesai'], 0, 5) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $badgeClass ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if (!$hasRow): ?>
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p>Belum ada pengajuan peminjaman.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
// Sidebar toggle
const sidebar  = document.getElementById('sidebar');
const overlay  = document.getElementById('overlay');
const toggleBtn = document.getElementById('toggleSidebar');

toggleBtn?.addEventListener('click', () => {
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
});

overlay.addEventListener('click', () => {
    sidebar.classList.remove('show');
    overlay.classList.remove('show');
});

// AJAX auto-refresh
function loadDashboard() {
    fetch('ajax_dashboard_mhs.php')
        .then(response => response.json())
        .then(data => {

            if (data.error) {
                alert("Session habis, silakan login kembali.");
                window.location.href = 'login_mhs.php';
                return;
            }

            // Update stat cards
            document.getElementById('total_pinjam').textContent   = data.total_pinjam;
            document.getElementById('total_menunggu').textContent  = data.total_menunggu;
            document.getElementById('total_disetujui').textContent = data.total_disetujui;
            document.getElementById('total_ditolak').textContent   = data.total_ditolak;

            // Update table
            let tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';

            if (data.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p>Belum ada pengajuan peminjaman.</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            data.data.forEach(row => {
                let statusClass = {
                    'menunggu':  'badge-menunggu',
                    'disetujui': 'badge-disetujui',
                    'ditolak':   'badge-ditolak'
                }[row.status] ?? 'badge-default';

                let tgl = new Date(row.tanggal).toLocaleDateString('id-ID', {
                    day: '2-digit', month: 'short', year: 'numeric'
                });

                tbody.innerHTML += `
                    <tr>
                        <td>${row.nama_lab}</td>
                        <td>${tgl}</td>
                        <td><span class="time-range">${row.jam_mulai.substring(0,5)} – ${row.jam_selesai.substring(0,5)}</span></td>
                        <td><span class="badge ${statusClass}">${row.status.charAt(0).toUpperCase() + row.status.slice(1)}</span></td>
                    </tr>
                `;
            });

        })
        .catch(error => console.error('Error:', error));
}

loadDashboard();
setInterval(loadDashboard, 10000);
</script>

</body>
</html>
