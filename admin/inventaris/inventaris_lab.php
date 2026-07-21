<?php
require_once __DIR__ . '/../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: ../../index.php');
  exit;
}

$id_lab = $_GET['id_lab'] ?? null;
if (!$id_lab) { header('Location: ../lab/data_lab.php'); exit; }
$id_lab = mysqli_real_escape_string($koneksi, $id_lab);

$labRes = mysqli_query($koneksi, "SELECT * FROM data_lab WHERE id_lab = '$id_lab'");
$lab = mysqli_fetch_assoc($labRes);
if (!$lab) { header('Location: ../lab/data_lab.php'); exit; }

$JUMLAH_MEJA_STANDAR = 40;

// Pastikan 40 baris meja tersedia untuk lab ini (auto-generate jika belum ada)
$cekMeja = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM inventaris_meja WHERE id_lab = '$id_lab'");
$jmlMejaSaatIni = (int) mysqli_fetch_assoc($cekMeja)['jml'];

if ($jmlMejaSaatIni < $JUMLAH_MEJA_STANDAR) {
    for ($i = $jmlMejaSaatIni + 1; $i <= $JUMLAH_MEJA_STANDAR; $i++) {
        mysqli_query($koneksi, "INSERT IGNORE INTO inventaris_meja (id_lab, nomor_meja) VALUES ('$id_lab', '$i')");
    }
}

// Ambil data AC
$acQuery = mysqli_query($koneksi, "SELECT * FROM inventaris_ac WHERE id_lab = '$id_lab' ORDER BY nomor_ac ASC");
$jumlahAc       = mysqli_num_rows($acQuery);
$acNormal       = 0;
$acRusak        = 0;
$acRows         = [];
while ($ac = mysqli_fetch_assoc($acQuery)) {
    $acRows[] = $ac;
    if ($ac['kondisi'] === 'normal') { $acNormal++; } else { $acRusak++; }
}

// Ambil data meja
$mejaQuery = mysqli_query($koneksi, "SELECT * FROM inventaris_meja WHERE id_lab = '$id_lab' ORDER BY nomor_meja ASC");
$mejaRows = [];
while ($m = mysqli_fetch_assoc($mejaQuery)) { $mejaRows[] = $m; }

$jumlahMeja  = count($mejaRows);
$jumlahKursi = (int) ($lab['jumlah_kursi'] ?? $JUMLAH_MEJA_STANDAR);

// Label & kelas badge untuk kondisi
function kondisiLabel($val) {
    return match($val) {
        'normal'       => 'Normal',
        'rusak'        => 'Rusak',
        'instal_ulang' => 'Instal Ulang',
        'tidak_ada'    => 'Tidak Ada',
        default        => ucfirst($val),
    };
}
function kondisiClass($val) {
    return match($val) {
        'normal'       => 'cond-normal',
        'rusak'        => 'cond-rusak',
        'instal_ulang' => 'cond-instal',
        'tidak_ada'    => 'cond-tidak-ada',
        default        => '',
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inventaris <?= htmlspecialchars($lab['nama_lab']); ?> – LabSystem</title>

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
    --gray-soft:  #F1F1EF;
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

/* ── SIDEBAR (identik dengan ../lab/data_lab.php) ── */
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
.sidebar-logo { display: flex; align-items: center; gap: 10px; padding: 0 4px; margin-bottom: 32px; }
.sidebar-logo .logo-icon { width: 32px; height: 32px; background: var(--accent); border-radius: 8px; display: grid; place-items: center; flex-shrink: 0; }
.sidebar-logo .logo-icon i { color: #fff; font-size: 15px; }
.sidebar-logo-text strong { display: block; font-size: 13px; font-weight: 600; color: var(--text); }
.sidebar-logo-text span { font-size: 11px; color: var(--muted); }
.nav-section { font-size: 10px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); padding: 0 8px; margin-bottom: 6px; margin-top: 16px; }
.nav-section:first-of-type { margin-top: 0; }
.nav-item { list-style: none; }
.nav-link { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 7px; color: var(--muted); font-size: 13.5px; font-weight: 500; text-decoration: none; transition: background .15s, color .15s; margin-bottom: 1px; }
.nav-link i { font-size: 15px; width: 18px; text-align: center; }
.nav-link:hover { background: var(--bg); color: var(--text); }
.nav-link.active { background: var(--accent); color: #fff; }
.nav-link.danger { color: var(--red); }
.nav-link.danger:hover { background: var(--red-soft); color: var(--red); }
.sidebar-user { margin-top: auto; padding: 12px 10px; background: var(--bg); border-radius: var(--radius); display: flex; align-items: center; gap: 10px; }
.sidebar-user .avatar { width: 32px; height: 32px; background: var(--accent); border-radius: 50%; display: grid; place-items: center; flex-shrink: 0; font-size: 13px; font-weight: 600; color: #fff; font-family: 'DM Mono', monospace; }
.sidebar-user-info strong { display: block; font-size: 12.5px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px; }
.sidebar-user-info span { font-size: 11px; color: var(--muted); }

/* ── TOPBAR MOBILE ── */
.topbar { display: none; position: sticky; top: 0; z-index: 990; background: var(--surface); border-bottom: 1px solid var(--border); padding: 12px 16px; align-items: center; justify-content: space-between; }
.topbar-title { font-size: 14px; font-weight: 600; }
.btn-icon { width: 36px; height: 36px; border: 1px solid var(--border); background: var(--surface); border-radius: 8px; cursor: pointer; display: grid; place-items: center; font-size: 16px; color: var(--text); transition: background .15s; }
.btn-icon:hover { background: var(--bg); }

/* ── MAIN ── */
.main { margin-left: var(--sidebar-w); min-height: 100vh; padding: 32px 36px; }

.back-link { display: inline-flex; align-items: center; gap: 6px; color: var(--muted); text-decoration: none; font-size: 13px; margin-bottom: 14px; }
.back-link:hover { color: var(--text); }

/* ── PAGE HEADER ── */
.page-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 22px; flex-wrap: wrap; }
.page-header-left h1 { font-size: 20px; font-weight: 600; letter-spacing: -.3px; margin-bottom: 2px; }
.page-header-left p { font-size: 13px; color: var(--muted); }

/* ── SUMMARY CARDS ── */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 22px;
}
.summary-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.summary-icon {
    width: 38px; height: 38px;
    border-radius: 9px;
    display: grid;
    place-items: center;
    font-size: 16px;
    flex-shrink: 0;
}
.summary-icon.kursi { background: var(--blue-soft); color: var(--blue); }
.summary-icon.meja { background: var(--amber-soft); color: var(--amber); }
.summary-icon.ac-ok { background: var(--green-soft); color: var(--green); }
.summary-icon.ac-rusak { background: var(--red-soft); color: var(--red); }
.summary-info span { display: block; font-size: 11.5px; color: var(--muted); margin-bottom: 1px; }
.summary-info strong { font-size: 18px; font-weight: 600; font-family: 'DM Mono', monospace; }
.summary-edit-btn {
    margin-left: auto;
    width: 26px; height: 26px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--muted);
    display: grid;
    place-items: center;
    cursor: pointer;
    font-size: 12px;
    flex-shrink: 0;
}
.summary-edit-btn:hover { background: var(--bg); color: var(--text); }

/* ── CARD ── */
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; margin-bottom: 20px; }
.card-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--border); gap: 12px; flex-wrap: wrap; }
.card-header-left h2 { font-size: 13.5px; font-weight: 600; color: var(--text); margin-bottom: 1px; }
.card-header-left span { font-size: 12px; color: var(--muted); }
.count-chip { display: inline-flex; align-items: center; padding: 2px 8px; background: var(--bg); border: 1px solid var(--border); border-radius: 100px; font-size: 11.5px; font-weight: 600; color: var(--muted); font-family: 'DM Mono', monospace; }

.btn-primary {
    display: inline-flex; align-items: center; gap: 6px; padding: 7px 13px;
    background: var(--accent); color: #fff; font-family: 'DM Sans', sans-serif;
    font-size: 12.5px; font-weight: 500; border: none; border-radius: 7px;
    text-decoration: none; cursor: pointer; transition: opacity .15s; white-space: nowrap;
}
.btn-primary:hover { opacity: .85; color: #fff; }

.btn-secondary-page {
    display: inline-flex; align-items: center; gap: 6px; padding: 7px 13px;
    background: var(--surface); color: var(--text); font-family: 'DM Sans', sans-serif;
    font-size: 12.5px; font-weight: 500; border: 1px solid var(--border); border-radius: 7px;
    text-decoration: none; cursor: pointer; transition: background .15s; white-space: nowrap;
}
.btn-secondary-page:hover { background: var(--bg); }

/* ── TABLE ── */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
thead th { padding: 11px 18px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); background: var(--bg); border-bottom: 1px solid var(--border); white-space: nowrap; }
tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #FAFAF8; }
tbody td { padding: 12px 18px; font-size: 13px; color: var(--text); vertical-align: middle; }
.row-num { font-family: 'DM Mono', monospace; font-size: 12px; color: var(--muted); width: 44px; }

/* Kondisi select */
.cond-select {
    padding: 5px 26px 5px 10px;
    border-radius: 100px;
    border: 1px solid transparent;
    font-family: 'DM Sans', sans-serif;
    font-size: 11.5px;
    font-weight: 500;
    cursor: pointer;
    outline: none;
    appearance: none;
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 10px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%238C8C8A'/%3E%3C/svg%3E");
    min-width: 108px;
}
.cond-normal    { background-color: var(--green-soft); color: var(--green); }
.cond-rusak     { background-color: var(--red-soft);   color: var(--red); }
.cond-instal    { background-color: var(--amber-soft); color: var(--amber); }
.cond-tidak-ada { background-color: var(--gray-soft);  color: var(--muted); }

.save-indicator {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: var(--green);
    opacity: 0;
    transition: opacity .3s;
    margin-left: 6px;
}
.save-indicator.show { opacity: 1; }

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
.btn-action.btn-delete:hover { background: var(--red-soft); color: var(--red); border-color: var(--red); }

/* ── OVERLAY ── */
.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.3); z-index: 999; }

/* ── RESPONSIVE ── */
@media (max-width: 1100px) {
    .summary-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.show { transform: translateX(0); }
    .sidebar-overlay.show { display: block; }
    .topbar { display: flex; }
    .main { margin-left: 0; padding: 16px; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .page-header > * { width: 100%; }
    .summary-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
    .summary-card { padding: 12px 14px; }
    .cond-select { min-width: 92px; font-size: 11px; }
}
@media (max-width: 480px) {
    .summary-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- Overlay -->
<div class="sidebar-overlay" id="overlay"></div>

<!-- Mobile Topbar -->
<header class="topbar">
    <button class="btn-icon" id="toggleSidebar"><i class="bi bi-list"></i></button>
    <span class="topbar-title">Inventaris Lab</span>
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
        <li class="nav-item"><a class="nav-link" href="../dashboard.php"><i class="bi bi-grid-1x2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="../lab/data_lab.php"><i class="bi bi-building-fill"></i> Laboratorium</a></li>
        <li class="nav-item"><a class="nav-link" href="../mahasiswa/data_mhs.php"><i class="bi bi-people-fill"></i> Mahasiswa</a></li>
    </ul>

    <p class="nav-section">Peminjaman</p>
    <ul style="list-style:none;padding:0;margin:0">
        <li class="nav-item"><a class="nav-link" href="../peminjaman/riwayat_pinjam.php"><i class="bi bi-clock-history"></i> Ongoing</a></li>
        <li class="nav-item"><a class="nav-link" href="../peminjaman/arsip_peminjaman.php"><i class="bi bi-archive-fill"></i> Arsip</a></li>
    </ul>

    <div style="flex:1"></div>

    <ul style="list-style:none;padding:0;margin:0 0 12px">
        <li class="nav-item">
            <a class="nav-link danger" href="../../auth/logout.php" onclick="return confirm('Yakin ingin logout?')">
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

    <a href="../lab/data_lab.php" class="back-link"><i class="bi bi-arrow-left"></i> Kembali ke Data Laboratorium</a>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Inventaris — <?= htmlspecialchars($lab['nama_lab']); ?></h1>
            <p>Kelola jumlah kursi, meja, AC, dan perangkat di setiap meja</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="riwayat_inventaris.php?id_lab=<?= urlencode($id_lab); ?>" class="btn-secondary-page">
                <i class="bi bi-clock-history"></i> Riwayat Bulanan
            </a>
            <a href="export_inventaris.php?id_lab=<?= urlencode($id_lab); ?>" class="btn-primary">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-icon kursi"><i class="bi bi-menu-app"></i></div>
            <div class="summary-info">
                <span>Jumlah Kursi</span>
                <strong id="jumlahKursiText"><?= $jumlahKursi; ?></strong>
            </div>
            <button class="summary-edit-btn" id="editKursiBtn" title="Ubah jumlah kursi"><i class="bi bi-pencil"></i></button>
        </div>
        <div class="summary-card">
            <div class="summary-icon meja"><i class="bi bi-display"></i></div>
            <div class="summary-info">
                <span>Jumlah Meja</span>
                <strong><?= $jumlahMeja; ?></strong>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon ac-ok"><i class="bi bi-snow2"></i></div>
            <div class="summary-info">
                <span>AC Normal</span>
                <strong><?= $acNormal; ?> <span style="font-size:12px;color:var(--muted);font-weight:400;">/ <?= $jumlahAc; ?></span></strong>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon ac-rusak"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="summary-info">
                <span>AC Rusak</span>
                <strong><?= $acRusak; ?></strong>
            </div>
        </div>
    </div>

    <!-- AC Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <h2>Inventaris AC</h2>
                <span>Kondisi setiap unit AC di laboratorium ini</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="count-chip"><?= $jumlahAc; ?> unit</span>
                <form method="POST" action="tambah_ac.php" style="margin:0;">
                    <input type="hidden" name="id_lab" value="<?= htmlspecialchars($id_lab); ?>">
                    <button type="submit" class="btn-primary"><i class="bi bi-plus"></i> Tambah AC</button>
                </form>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:44px">#</th>
                        <th>Unit AC</th>
                        <th>Kondisi</th>
                        <th style="width:60px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($jumlahAc > 0): ?>
                    <?php foreach ($acRows as $ac): ?>
                    <tr>
                        <td class="row-num"><?= $ac['nomor_ac']; ?></td>
                        <td>AC Unit #<?= $ac['nomor_ac']; ?></td>
                        <td>
                            <select class="cond-select <?= kondisiClass($ac['kondisi']); ?>"
                                    data-type="ac" data-id="<?= $ac['id_ac']; ?>" data-field="kondisi">
                                <option value="normal" <?= $ac['kondisi']==='normal'?'selected':''; ?>>Normal</option>
                                <option value="rusak" <?= $ac['kondisi']==='rusak'?'selected':''; ?>>Rusak</option>
                            </select>
                        </td>
                        <td>
                            <a href="hapus_ac.php?id_ac=<?= $ac['id_ac']; ?>&id_lab=<?= urlencode($id_lab); ?>"
                               class="btn-action btn-delete" title="Hapus AC"
                               onclick="return confirm('Hapus unit AC #<?= $ac['nomor_ac']; ?>?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:28px;">Belum ada data AC. Klik "Tambah AC" untuk menambahkan.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Meja Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <h2>Inventaris Meja & Perangkat</h2>
                <span>CPU, keyboard, mouse, monitor, dan kursi per meja (<?= $JUMLAH_MEJA_STANDAR; ?> meja)</span>
            </div>
            <span class="count-chip"><?= $jumlahMeja; ?> meja</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:44px">#</th>
                        <th>CPU</th>
                        <th>Keyboard</th>
                        <th>Mouse</th>
                        <th>Monitor</th>
                        <th>Kursi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($mejaRows as $m): ?>
                    <tr>
                        <td class="row-num"><?= $m['nomor_meja']; ?></td>
                        <td>
                            <select class="cond-select <?= kondisiClass($m['cpu_kondisi']); ?>"
                                    data-type="meja" data-id="<?= $m['id_meja']; ?>" data-field="cpu_kondisi">
                                <option value="normal" <?= $m['cpu_kondisi']==='normal'?'selected':''; ?>>Normal</option>
                                <option value="rusak" <?= $m['cpu_kondisi']==='rusak'?'selected':''; ?>>Rusak</option>
                                <option value="instal_ulang" <?= $m['cpu_kondisi']==='instal_ulang'?'selected':''; ?>>Instal Ulang</option>
                            </select>
                        </td>
                        <td>
                            <select class="cond-select <?= kondisiClass($m['keyboard_kondisi']); ?>"
                                    data-type="meja" data-id="<?= $m['id_meja']; ?>" data-field="keyboard_kondisi">
                                <option value="normal" <?= $m['keyboard_kondisi']==='normal'?'selected':''; ?>>Normal</option>
                                <option value="rusak" <?= $m['keyboard_kondisi']==='rusak'?'selected':''; ?>>Rusak</option>
                                <option value="tidak_ada" <?= $m['keyboard_kondisi']==='tidak_ada'?'selected':''; ?>>Tidak Ada</option>
                            </select>
                        </td>
                        <td>
                            <select class="cond-select <?= kondisiClass($m['mouse_kondisi']); ?>"
                                    data-type="meja" data-id="<?= $m['id_meja']; ?>" data-field="mouse_kondisi">
                                <option value="normal" <?= $m['mouse_kondisi']==='normal'?'selected':''; ?>>Normal</option>
                                <option value="rusak" <?= $m['mouse_kondisi']==='rusak'?'selected':''; ?>>Rusak</option>
                                <option value="tidak_ada" <?= $m['mouse_kondisi']==='tidak_ada'?'selected':''; ?>>Tidak Ada</option>
                            </select>
                        </td>
                        <td>
                            <select class="cond-select <?= kondisiClass($m['monitor_kondisi']); ?>"
                                    data-type="meja" data-id="<?= $m['id_meja']; ?>" data-field="monitor_kondisi">
                                <option value="normal" <?= $m['monitor_kondisi']==='normal'?'selected':''; ?>>Normal</option>
                                <option value="rusak" <?= $m['monitor_kondisi']==='rusak'?'selected':''; ?>>Rusak</option>
                                <option value="tidak_ada" <?= $m['monitor_kondisi']==='tidak_ada'?'selected':''; ?>>Tidak Ada</option>
                            </select>
                        </td>
                        <td>
                            <select class="cond-select <?= kondisiClass($m['kursi_kondisi']); ?>"
                                    data-type="meja" data-id="<?= $m['id_meja']; ?>" data-field="kursi_kondisi">
                                <option value="normal" <?= $m['kursi_kondisi']==='normal'?'selected':''; ?>>Normal</option>
                                <option value="rusak" <?= $m['kursi_kondisi']==='rusak'?'selected':''; ?>>Rusak</option>
                                <option value="tidak_ada" <?= $m['kursi_kondisi']==='tidak_ada'?'selected':''; ?>>Tidak Ada</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
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

// Warna kelas berdasarkan value select kondisi
const condClassMap = {
    normal: 'cond-normal',
    rusak: 'cond-rusak',
    instal_ulang: 'cond-instal',
    tidak_ada: 'cond-tidak-ada'
};

function applyCondClass(select) {
    Object.values(condClassMap).forEach(c => select.classList.remove(c));
    const cls = condClassMap[select.value];
    if (cls) select.classList.add(cls);
}

async function saveCondition(select) {
    const payload = {
        type: select.dataset.type,
        id: select.dataset.id,
        field: select.dataset.field,
        value: select.value
    };
    try {
        const res = await fetch('update_inventaris.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await res.json();
        if (result.success) {
            applyCondClass(select);
        } else {
            alert('Gagal menyimpan: ' + (result.message || 'terjadi kesalahan'));
        }
    } catch (err) {
        alert('Gagal menyimpan perubahan. Periksa koneksi Anda.');
    }
}

document.querySelectorAll('.cond-select').forEach(select => {
    select.addEventListener('change', () => saveCondition(select));
});

// Edit jumlah kursi
const editKursiBtn = document.getElementById('editKursiBtn');
const jumlahKursiText = document.getElementById('jumlahKursiText');

editKursiBtn.addEventListener('click', async () => {
    const current = jumlahKursiText.textContent.trim();
    const input = prompt('Masukkan jumlah kursi baru:', current);
    if (input === null) return;
    const value = parseInt(input, 10);
    if (isNaN(value) || value < 0) {
        alert('Masukkan angka yang valid.');
        return;
    }
    try {
        const res = await fetch('update_inventaris.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: 'lab', id: '<?= $id_lab; ?>', field: 'jumlah_kursi', value: value })
        });
        const result = await res.json();
        if (result.success) {
            jumlahKursiText.textContent = value;
        } else {
            alert('Gagal menyimpan: ' + (result.message || 'terjadi kesalahan'));
        }
    } catch (err) {
        alert('Gagal menyimpan perubahan. Periksa koneksi Anda.');
    }
});
</script>

</body>
</html>