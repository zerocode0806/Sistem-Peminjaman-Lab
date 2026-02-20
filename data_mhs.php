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
   AMBIL DATA MAHASISWA
================================ */
$query = mysqli_query($koneksi, "
    SELECT *
    FROM mahasiswa
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Data Mahasiswa – LabSystem</title>

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
    --red:       #DC2626;
    --red-soft:  #FEF2F2;
    --blue:      #2563EB;
    --blue-soft: #EFF4FF;
    --warn:      #D97706;
    --warn-soft: #FFFBEB;
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

.sidebar-logo .logo-icon i { color: #fff; font-size: 15px; }

.sidebar-logo-text strong {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.sidebar-logo-text span { font-size: 11px; color: var(--muted); }

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
.nav-link.danger { color: var(--red); }
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

.sidebar-user-info span { font-size: 11px; color: var(--muted); }

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

/* ── MAIN ── */
.main {
    margin-left: var(--sidebar-w);
    min-height: 100vh;
    padding: 32px 36px;
}

.page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.page-header-left h1 {
    font-size: 20px;
    font-weight: 600;
    letter-spacing: -.3px;
    margin-bottom: 2px;
}

.page-header-left p { font-size: 13px; color: var(--muted); }

/* ── SEARCH BAR ── */
.search-wrap {
    position: relative;
    width: 220px;
}

.search-wrap i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted);
    font-size: 13px;
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 8px 12px 8px 32px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    color: var(--text);
    outline: none;
    transition: border-color .15s;
}

.search-input:focus { border-color: var(--accent); }
.search-input::placeholder { color: var(--muted); }

/* ── CARD ── */
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
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}

.card-header-left h2 {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 1px;
}

.card-header-left span { font-size: 12px; color: var(--muted); }

/* ── BUTTONS ── */
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

.nim-badge {
    font-family: 'DM Mono', monospace;
    font-size: 12px;
    font-weight: 500;
    color: var(--text);
    background: var(--bg);
    padding: 3px 8px;
    border-radius: 5px;
    border: 1px solid var(--border);
    display: inline-block;
}

.phone-text {
    font-family: 'DM Mono', monospace;
    font-size: 12.5px;
    color: var(--muted);
}

.address-text {
    font-size: 13px;
    color: var(--muted);
    max-width: 220px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ACTION BUTTONS */
.action-group {
    display: inline-flex;
    gap: 6px;
    align-items: center;
    justify-content: flex-end;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 10px;
    border-radius: 6px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    border: 1px solid transparent;
    transition: background .15s, border-color .15s;
    white-space: nowrap;
    cursor: pointer;
}

.btn-action i { font-size: 12px; }

.btn-detail {
    background: var(--surface);
    border-color: var(--border);
    color: var(--text);
}
.btn-detail:hover { background: var(--bg); color: var(--text); }

.btn-edit {
    background: var(--warn-soft);
    border-color: #FDE68A;
    color: var(--warn);
}
.btn-edit:hover { background: #FEF3C7; color: var(--warn); }

.btn-delete {
    background: var(--red-soft);
    border-color: #FECACA;
    color: var(--red);
}
.btn-delete:hover { background: #FEE2E2; color: var(--red); }

/* ── EMPTY STATE ── */
.empty-state {
    text-align: center;
    padding: 56px 20px;
    color: var(--muted);
}

.empty-state .empty-icon {
    width: 52px; height: 52px;
    background: var(--bg);
    border-radius: 50%;
    display: grid;
    place-items: center;
    margin: 0 auto 14px;
    font-size: 22px;
    color: var(--muted);
}

.empty-state p { font-size: 13px; }

/* ── COUNT CHIP ── */
.count-chip {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 100px;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--muted);
    font-family: 'DM Mono', monospace;
}

/* ── OVERLAY ── */
.sidebar-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.3);
    z-index: 999;
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.show { transform: translateX(0); }
    .sidebar-overlay.show { display: block; }
    .topbar { display: flex; }
    .main { margin-left: 0; padding: 16px; }
    .search-wrap { width: 100%; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .page-header > * { width: 100%; }
    thead th:nth-child(3),
    thead th:nth-child(4),
    tbody td:nth-child(3),
    tbody td:nth-child(4) { display: none; }
    .btn-action span { display: none; }
    .btn-action { padding: 6px 8px; }
}
</style>
</head>
<body>

<!-- Overlay -->
<div class="sidebar-overlay" id="overlay"></div>

<!-- Mobile Topbar -->
<header class="topbar">
    <button class="btn-icon" id="toggleSidebar">
        <i class="bi bi-list"></i>
    </button>
    <span class="topbar-title">Data Mahasiswa</span>
    <div style="width:36px"></div>
</header>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon"><i class="bi bi-boxes"></i></div>
        <div class="sidebar-logo-text">
            <strong>LabSystem</strong>
            <span>Admin Panel</span>
        </div>
    </div>

    <p class="nav-section">Menu</p>
    <ul style="list-style:none;padding:0;margin:0">
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="data_lab.php">
                <i class="bi bi-building-fill"></i> Laboratorium
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="data_mhs.php">
                <i class="bi bi-people-fill"></i> Mahasiswa
            </a>
        </li>
    </ul>

    <p class="nav-section">Peminjaman</p>
    <ul style="list-style:none;padding:0;margin:0">
        <li class="nav-item">
            <a class="nav-link" href="riwayat_pinjam.php">
                <i class="bi bi-clock-history"></i> Riwayat
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="arsip_peminjaman.php">
                <i class="bi bi-archive-fill"></i> Arsip
            </a>
        </li>
    </ul>

    <div style="flex:1"></div>

    <ul style="list-style:none;padding:0;margin:0 0 12px">
        <li class="nav-item">
            <a class="nav-link danger" href="logout.php" onclick="return confirm('Yakin ingin logout?')">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </li>
    </ul>

    <div class="sidebar-user">
        <div class="avatar"><?= strtoupper(substr($_SESSION['user']['nama'], 0, 1)) ?></div>
        <div class="sidebar-user-info">
            <strong><?= htmlspecialchars($_SESSION['user']['nama']) ?></strong>
            <span>Administrator</span>
        </div>
    </div>
</aside>

<!-- Main -->
<main class="main">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>Data Mahasiswa</h1>
            <p>Kelola daftar mahasiswa yang terdaftar dalam sistem</p>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Cari nama atau NIM…">
            </div>
            <a href="login_mhs.php" class="btn-primary">
                <i class="bi bi-plus"></i>
                <span>Tambah Mahasiswa</span>
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <h2>Daftar Mahasiswa</h2>
                <span>Semua mahasiswa yang terdaftar</span>
            </div>
            <span class="count-chip" id="rowCount">—</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>No. Telepon</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                <?php if (mysqli_num_rows($query) == 0): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-people"></i></div>
                                <p>Belum ada data mahasiswa yang terdaftar.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td>
                            <span style="font-weight:500"><?= htmlspecialchars($row['nama']) ?></span>
                        </td>
                        <td>
                            <span class="nim-badge"><?= htmlspecialchars($row['nim']) ?></span>
                        </td>
                        <td>
                            <span class="phone-text"><?= htmlspecialchars($row['no_telepon']) ?></span>
                        </td>
                        <td>
                            <span class="address-text" title="<?= htmlspecialchars($row['alamat']) ?>">
                                <?= htmlspecialchars($row['alamat']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="detail_data_mhs.php?nim=<?= $row['nim'] ?>" class="btn-action btn-detail">
                                    <i class="bi bi-arrow-right"></i>
                                    <span>Detail</span>
                                </a>
                                <a href="ubah_mhs.php?id=<?= $row['nim'] ?>" class="btn-action btn-edit">
                                    <i class="bi bi-pencil"></i>
                                    <span>Ubah</span>
                                </a>
                                <a href="hapus_mhs.php?id=<?= $row['nim'] ?>"
                                   class="btn-action btn-delete"
                                   onclick="return confirm('Yakin ingin menghapus mahasiswa ini?')">
                                    <i class="bi bi-trash"></i>
                                    <span>Hapus</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
// Sidebar toggle
const sidebar   = document.getElementById('sidebar');
const overlay   = document.getElementById('overlay');
const toggleBtn = document.getElementById('toggleSidebar');

toggleBtn?.addEventListener('click', () => {
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
});

overlay.addEventListener('click', () => {
    sidebar.classList.remove('show');
    overlay.classList.remove('show');
});

// Row count
const tableBody  = document.getElementById('tableBody');
const rowCount   = document.getElementById('rowCount');
const allRows    = Array.from(tableBody.querySelectorAll('tr[data-searchable]'));

function updateCount(visible) {
    rowCount.textContent = visible + ' mahasiswa';
}

// Tag rows for searching
document.querySelectorAll('#tableBody tr').forEach(row => {
    const cells = row.querySelectorAll('td');
    if (cells.length > 1) {
        row.setAttribute('data-searchable', '');
        row.setAttribute('data-search-text', row.textContent.toLowerCase());
    }
});

const searchableRows = Array.from(tableBody.querySelectorAll('tr[data-searchable]'));
updateCount(searchableRows.length);

// Search
document.getElementById('searchInput').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    let visible = 0;
    searchableRows.forEach(row => {
        const match = row.getAttribute('data-search-text').includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    updateCount(visible);
});
</script>

</body>
</html>