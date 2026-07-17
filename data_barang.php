<?php
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

// Filter opsional berdasarkan lab (misal datang dari tombol di data_lab.php)
$filterLab = isset($_GET['id_lab']) && $_GET['id_lab'] !== '' ? $_GET['id_lab'] : null;

if ($filterLab !== null) {
    $stmt = mysqli_prepare($koneksi,
        "SELECT b.*, l.nama_lab
         FROM data_barang b
         JOIN data_lab l ON b.id_lab = l.id_lab
         WHERE b.id_lab = ?
         ORDER BY b.nama_barang ASC");
    mysqli_stmt_bind_param($stmt, "s", $filterLab);
    mysqli_stmt_execute($stmt);
    $query = mysqli_stmt_get_result($stmt);
} else {
    $query = mysqli_query($koneksi,
        "SELECT b.*, l.nama_lab
         FROM data_barang b
         JOIN data_lab l ON b.id_lab = l.id_lab
         ORDER BY b.nama_barang ASC");
}

// Untuk dropdown filter lab
$daftarLab = mysqli_query($koneksi, "SELECT id_lab, nama_lab FROM data_lab ORDER BY nama_lab ASC");

$activeLabName = null;
if ($filterLab !== null) {
    $stmtL = mysqli_prepare($koneksi, "SELECT nama_lab FROM data_lab WHERE id_lab = ?");
    mysqli_stmt_bind_param($stmtL, "s", $filterLab);
    mysqli_stmt_execute($stmtL);
    $r = mysqli_stmt_get_result($stmtL)->fetch_assoc();
    $activeLabName = $r['nama_lab'] ?? null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Data Barang – LabSystem</title>

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
    --blue-soft:  #EFF6FF;
    --amber:      #F59E0B;
    --amber-soft: #FFFBEB;
    --violet:     #7C3AED;
    --violet-soft:#F5F3FF;
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
    margin-bottom: 18px;
    flex-wrap: wrap;
}

.page-header-left h1 {
    font-size: 20px;
    font-weight: 600;
    letter-spacing: -.3px;
    margin-bottom: 2px;
}

.page-header-left p { font-size: 13px; color: var(--muted); }

/* Active filter chip */
.filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: var(--violet-soft);
    color: var(--violet);
    border-radius: 100px;
    font-size: 12.5px;
    font-weight: 500;
    margin-bottom: 18px;
}
.filter-chip a { color: var(--violet); display: inline-flex; align-items: center; margin-left: 2px; }

/* ── TOOLBAR ── */
.toolbar {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 18px;
}

.search-wrap { position: relative; width: 220px; }

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

.filter-select {
    padding: 8px 12px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    color: var(--text);
    outline: none;
    cursor: pointer;
}

.spacer { flex: 1; }

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
}

/* ── TABLE ── */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }

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

tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #FAFAF8; }

tbody td {
    padding: 14px 20px;
    font-size: 13.5px;
    color: var(--text);
    vertical-align: middle;
}

.row-num { font-family: 'DM Mono', monospace; font-size: 12px; color: var(--muted); width: 48px; }

.item-cell { display: flex; flex-direction: column; gap: 3px; }
.item-name { font-weight: 600; font-size: 13.5px; color: var(--text); }
.item-meta { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }

.meta-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-family: 'DM Mono', monospace;
    font-size: 11px;
    color: var(--muted);
    background: var(--bg);
    border: 1px solid var(--border);
    padding: 2px 7px;
    border-radius: 4px;
}

.meta-tag i { font-size: 10px; }

.tag-kategori {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    background: var(--violet-soft);
    color: var(--violet);
    border-radius: 5px;
    font-size: 11px;
    font-weight: 500;
}

.lab-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12.5px;
    color: var(--text);
    text-decoration: none;
    padding: 4px 9px;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--bg);
    transition: border-color .15s, color .15s;
}
.lab-link:hover { border-color: var(--accent); color: var(--accent); }
.lab-link i { font-size: 11px; color: var(--muted); }

/* Status / kondisi badge */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 100px;
    font-size: 11.5px;
    font-weight: 500;
    white-space: nowrap;
}

.badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }

.badge-available { background: var(--green-soft); color: var(--green); }
.badge-available::before { background: var(--green); }

.badge-unavailable { background: var(--red-soft); color: var(--red); }
.badge-unavailable::before { background: var(--red); }

.badge-baik { background: var(--green-soft); color: var(--green); }
.badge-baik::before { background: var(--green); }

.badge-rusak { background: var(--red-soft); color: var(--red); }
.badge-rusak::before { background: var(--red); }

.badge-perbaikan { background: var(--amber-soft); color: var(--amber); }
.badge-perbaikan::before { background: var(--amber); }

/* Stok indicator */
.stok-bar-wrap { display: flex; align-items: center; gap: 8px; }

.stok-bar {
    flex: 1;
    max-width: 70px;
    height: 5px;
    background: var(--border);
    border-radius: 100px;
    overflow: hidden;
}

.stok-bar-fill { height: 100%; border-radius: 100px; background: var(--green); transition: width .3s; }
.stok-bar-fill.low { background: #F59E0B; }
.stok-bar-fill.empty { background: var(--red); }

.stok-num {
    font-family: 'DM Mono', monospace;
    font-size: 12.5px;
    font-weight: 500;
    color: var(--text);
    min-width: 20px;
}

/* ── ACTION BUTTONS ── */
.action-buttons { display: flex; align-items: center; gap: 6px; }

.btn-action {
    width: 30px; height: 30px;
    display: grid;
    place-items: center;
    border-radius: 7px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--muted);
    font-size: 13px;
    text-decoration: none;
    cursor: pointer;
    transition: background .15s, color .15s, border-color .15s;
}

.btn-action.btn-edit:hover { background: var(--blue-soft); color: var(--blue); border-color: var(--blue); }
.btn-action.btn-delete:hover { background: var(--red-soft); color: var(--red); border-color: var(--red); }

/* ── EMPTY STATE ── */
.empty-state { text-align: center; padding: 56px 20px; color: var(--muted); }

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

.empty-state p { font-size: 13px; margin-bottom: 14px; }

/* ── OVERLAY ── */
.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.3); z-index: 999; }

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.show { transform: translateX(0); }
    .sidebar-overlay.show { display: block; }
    .topbar { display: flex; }
    .main { margin-left: 0; padding: 16px; }
    .search-wrap { width: 100%; }
    .toolbar { flex-direction: column; align-items: stretch; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .page-header > * { width: 100%; }
    .stok-bar-wrap .stok-bar { display: none; }
    thead th:nth-child(4),
    tbody td:nth-child(4) { display: none; }
    .btn-action { width: 32px; height: 32px; }
}
</style>
</head>
<body>

<div class="sidebar-overlay" id="overlay"></div>

<header class="topbar">
    <button class="btn-icon" id="toggleSidebar"><i class="bi bi-list"></i></button>
    <span class="topbar-title">Data Barang</span>
    <div style="width:36px"></div>
</header>

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
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-grid-1x2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="data_lab.php"><i class="bi bi-building-fill"></i> Laboratorium</a></li>
        <li class="nav-item"><a class="nav-link active" href="data_barang.php"><i class="bi bi-box-seam-fill"></i> Data Barang</a></li>
        <li class="nav-item"><a class="nav-link" href="data_mhs.php"><i class="bi bi-people-fill"></i> Mahasiswa</a></li>
    </ul>

    <p class="nav-section">Peminjaman</p>
    <ul style="list-style:none;padding:0;margin:0">
        <li class="nav-item"><a class="nav-link" href="riwayat_pinjam.php"><i class="bi bi-clock-history"></i> Ongoing</a></li>
        <li class="nav-item"><a class="nav-link" href="arsip_peminjaman.php"><i class="bi bi-archive-fill"></i> Arsip</a></li>
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

<main class="main">

    <div class="page-header">
        <div class="page-header-left">
            <h1>Data Barang</h1>
            <p>Kelola dan catat seluruh inventaris barang milik laboratorium</p>
        </div>
        <a href="tambah_data_barang.php<?= $filterLab !== null ? '?id_lab=' . urlencode($filterLab) : ''; ?>" class="btn-primary">
            <i class="bi bi-plus"></i>
            <span>Tambah Barang</span>
        </a>
    </div>

    <?php if ($filterLab !== null): ?>
    <div class="filter-chip">
        <i class="bi bi-funnel-fill"></i>
        Menampilkan barang dari: <?= htmlspecialchars($activeLabName ?? $filterLab); ?>
        <a href="data_barang.php" title="Hapus filter"><i class="bi bi-x-circle-fill"></i></a>
    </div>
    <?php endif; ?>

    <div class="toolbar">
        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" class="search-input" id="searchInput" placeholder="Cari nama, kode, atau kategori barang…">
        </div>
        <select class="filter-select" id="labFilterSelect" onchange="if(this.value){window.location='data_barang.php?id_lab='+encodeURIComponent(this.value);}else{window.location='data_barang.php';}">
            <option value="">Semua Lab</option>
            <?php while ($l = mysqli_fetch_assoc($daftarLab)): ?>
                <option value="<?= htmlspecialchars($l['id_lab']); ?>" <?= ($filterLab == $l['id_lab']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($l['nama_lab']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <div class="spacer"></div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <h2>Daftar Barang</h2>
                <span>Diurutkan berdasarkan nama</span>
            </div>
            <span class="count-chip" id="rowCount">—</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:48px">#</th>
                        <th>Barang</th>
                        <th>Lab</th>
                        <th>Stok</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th style="width:100px">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                <?php
                $no = 1;
                if (mysqli_num_rows($query) > 0):
                    while ($barang = mysqli_fetch_assoc($query)):

                        $badgeStatus = $barang['status'] === 'availabel' ? 'badge-available' : 'badge-unavailable';
                        $statusText  = $barang['status'] === 'availabel' ? 'Tersedia' : 'Tidak Tersedia';

                        $badgeKondisi = 'badge-' . $barang['kondisi'];
                        $kondisiText  = ucfirst($barang['kondisi']);

                        $stok = (int)$barang['stok'];
                        $barClass = $stok <= 0 ? 'empty' : ($stok <= 3 ? 'low' : '');
                        $barPct = min(100, ($stok / 20) * 100);
                ?>
                    <tr>
                        <td class="row-num"><?= $no++; ?></td>
                        <td>
                            <div class="item-cell">
                                <span class="item-name"><?= htmlspecialchars($barang['nama_barang']); ?></span>
                                <div class="item-meta">
                                    <span class="meta-tag">
                                        <i class="bi bi-hash"></i>
                                        <?= htmlspecialchars($barang['kode_barang']); ?>
                                    </span>
                                    <?php if (!empty($barang['kategori'])): ?>
                                    <span class="tag-kategori"><?= htmlspecialchars($barang['kategori']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a class="lab-link" href="data_barang.php?id_lab=<?= urlencode($barang['id_lab']); ?>">
                                <i class="bi bi-building"></i> <?= htmlspecialchars($barang['nama_lab']); ?>
                            </a>
                        </td>
                        <td>
                            <div class="stok-bar-wrap">
                                <span class="stok-num"><?= $stok; ?></span>
                                <div class="stok-bar">
                                    <div class="stok-bar-fill <?= $barClass; ?>" style="width:<?= $barPct; ?>%"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $badgeKondisi; ?>"><?= $kondisiText; ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $badgeStatus; ?>"><?= $statusText; ?></span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_data_barang.php?id=<?= urlencode($barang['id_barang']); ?>"
                                   class="btn-action btn-edit" title="Edit Barang">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="hapus_data_barang.php?id=<?= urlencode($barang['id_barang']); ?><?= $filterLab !== null ? '&id_lab=' . urlencode($filterLab) : ''; ?>"
                                   class="btn-action btn-delete" title="Hapus Barang"
                                   onclick="return confirm('Yakin ingin menghapus barang \'<?= htmlspecialchars(addslashes($barang['nama_barang'])); ?>\'? Tindakan ini tidak dapat dibatalkan.')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-box-seam"></i></div>
                                <p>Belum ada data barang<?= $filterLab !== null ? ' untuk lab ini' : ''; ?>.</p>
                                <a href="tambah_data_barang.php<?= $filterLab !== null ? '?id_lab=' . urlencode($filterLab) : ''; ?>" class="btn-primary">
                                    <i class="bi bi-plus"></i><span>Tambah Barang</span>
                                </a>
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

const tableBody = document.getElementById('tableBody');
const rowCount  = document.getElementById('rowCount');

document.querySelectorAll('#tableBody tr').forEach(row => {
    if (row.querySelectorAll('td').length > 1) {
        row.setAttribute('data-searchable', '');
        row.setAttribute('data-search-text', row.textContent.toLowerCase());
    }
});

const searchableRows = Array.from(tableBody.querySelectorAll('tr[data-searchable]'));
rowCount.textContent = searchableRows.length + ' barang';

document.getElementById('searchInput').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    let visible = 0;
    searchableRows.forEach(row => {
        const match = row.getAttribute('data-search-text').includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    rowCount.textContent = visible + ' barang';
});
</script>

</body>
</html>