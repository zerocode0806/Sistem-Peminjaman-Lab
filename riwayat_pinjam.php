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
<title>Riwayat Peminjaman – LabSystem</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg:         #F7F7F5;
    --surface:    #FFFFFF;
    --border:     #E8E8E3;
    --text:       #18181B;
    --muted:      #8C8C8A;
    --accent:     #1A1A1A;
    --red:        #DC2626;
    --red-soft:   #FEF2F2;
    --green:      #16A34A;
    --green-soft: #F0FDF4;
    --blue:       #2563EB;
    --blue-soft:  #EFF4FF;
    --sidebar-w:  228px;
    --radius:     10px;
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

/* ── PAGE HEADER ── */
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

/* ── TOOLBAR ── */
.toolbar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* ── SEARCH ── */
.search-wrap {
    position: relative;
    width: 240px;
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
    white-space: nowrap;
}

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

.nim-text {
    font-family: 'DM Mono', monospace;
    font-size: 12.5px;
    font-weight: 500;
    color: var(--text);
}

.time-range {
    font-family: 'DM Mono', monospace;
    font-size: 12px;
    color: var(--muted);
    background: var(--bg);
    border: 1px solid var(--border);
    padding: 3px 8px;
    border-radius: 5px;
    display: inline-block;
    white-space: nowrap;
}

/* ── STATUS BADGE ── */
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

.badge-disetujui { background: var(--green-soft); color: var(--green); }
.badge-disetujui::before { background: var(--green); }
.badge-ditolak   { background: var(--red-soft);   color: var(--red); }
.badge-ditolak::before   { background: var(--red); }
.badge-default   { background: var(--bg); color: var(--muted); border: 1px solid var(--border); }
.badge-default::before   { background: var(--muted); }

/* ── ACTION BUTTON ── */
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
    white-space: nowrap;
}

.btn-detail:hover { background: var(--bg); border-color: #ccc; color: var(--text); }

/* ── NO-RESULT ROW ── */
.no-result-row td {
    text-align: center;
    padding: 52px 20px;
    color: var(--muted);
}

.no-result-row .empty-icon {
    width: 48px; height: 48px;
    background: var(--bg);
    border-radius: 50%;
    display: grid;
    place-items: center;
    margin: 0 auto 12px;
    font-size: 20px;
    border: 1px solid var(--border);
}

.no-result-row p { font-size: 13px; }

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
    .toolbar { width: 100%; }
    thead th:nth-child(3),
    thead th:nth-child(4),
    tbody td:nth-child(3),
    tbody td:nth-child(4) { display: none; }
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
    <span class="topbar-title">Riwayat Peminjaman</span>
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
            <a class="nav-link" href="data_mhs.php">
                <i class="bi bi-people-fill"></i> Mahasiswa
            </a>
        </li>
    </ul>

    <p class="nav-section">Peminjaman</p>
    <ul style="list-style:none;padding:0;margin:0">
        <li class="nav-item">
            <a class="nav-link active" href="riwayat_pinjam.php">
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
            <h1>Riwayat Peminjaman</h1>
            <p>Daftar peminjaman dengan status disetujui</p>
        </div>
        <div class="toolbar">
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Cari NIM atau laboratorium…">
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <h2>Peminjaman Disetujui</h2>
                <span>Semua peminjaman aktif yang sudah diproses</span>
            </div>
            <span class="count-chip" id="rowCount">—</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Laboratorium</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                <?php if (mysqli_num_rows($query) == 0): ?>
                    <tr class="no-result-row">
                        <td colspan="6">
                            <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                            <p>Belum ada riwayat peminjaman yang disetujui.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while ($row = mysqli_fetch_assoc($query)):
                        $badgeClass = match($row['status']) {
                            'disetujui' => 'badge-disetujui',
                            'ditolak'   => 'badge-ditolak',
                            default     => 'badge-default'
                        };
                    ?>
                    <tr>
                        <td><span class="nim-text"><?= htmlspecialchars($row['nim']) ?></span></td>
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
                        <td>
                            <a href="checkout_pinjam.php?id=<?= $row['id_data'] ?>" class="btn-detail">
                                <i class="bi bi-arrow-right"></i> Detail
                            </a>
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

// Tag rows + initial count
const tableBody = document.getElementById('tableBody');
const rowCount  = document.getElementById('rowCount');

document.querySelectorAll('#tableBody tr').forEach(row => {
    if (row.querySelectorAll('td').length > 1) {
        row.setAttribute('data-searchable', '');
        row.setAttribute('data-search-text', row.textContent.toLowerCase());
    }
});

const searchableRows = Array.from(tableBody.querySelectorAll('tr[data-searchable]'));
rowCount.textContent = searchableRows.length + ' data';

// Live search
document.getElementById('searchInput').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    let visible = 0;
    searchableRows.forEach(row => {
        const match = row.getAttribute('data-search-text').includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    rowCount.textContent = visible + ' data';

    // Show/hide empty search state
    let emptyRow = tableBody.querySelector('.search-empty-row');
    if (visible === 0 && searchableRows.length > 0) {
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.className = 'search-empty-row no-result-row';
            emptyRow.innerHTML = `<td colspan="6">
                <div class="empty-icon"><i class="bi bi-search"></i></div>
                <p>Tidak ada hasil untuk "<strong>${this.value}</strong>"</p>
            </td>`;
            tableBody.appendChild(emptyRow);
        } else {
            emptyRow.querySelector('p').innerHTML = `Tidak ada hasil untuk "<strong>${this.value}</strong>"`;
            emptyRow.style.display = '';
        }
    } else if (emptyRow) {
        emptyRow.style.display = 'none';
    }
});
</script>

</body>
</html>