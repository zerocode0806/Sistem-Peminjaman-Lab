<?php
require_once __DIR__ . '/../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: ../../index.php');
  exit;
}

$id_periode = $_GET['id_periode'] ?? null;
if (!$id_periode) { header('Location: ../lab/data_lab.php'); exit; }
$id_periode = mysqli_real_escape_string($koneksi, $id_periode);

$periodeRes = mysqli_query($koneksi, "SELECT p.*, l.nama_lab FROM periode_inventaris p
    JOIN data_lab l ON l.id_lab = p.id_lab
    WHERE p.id_periode = '$id_periode'");
$periode = mysqli_fetch_assoc($periodeRes);
if (!$periode) { header('Location: ../lab/data_lab.php'); exit; }

$id_lab = $periode['id_lab'];

$namaBulan = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
    7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];
$labelPeriode = $namaBulan[(int)$periode['bulan']] . ' ' . $periode['tahun'];

$acQuery   = mysqli_query($koneksi, "SELECT * FROM riwayat_ac WHERE id_periode = '$id_periode' ORDER BY nomor_ac ASC");
$mejaQuery = mysqli_query($koneksi, "SELECT * FROM riwayat_meja WHERE id_periode = '$id_periode' ORDER BY nomor_meja ASC");

function kondisiLabel($val) {
    return match($val) {
        'normal'       => 'Normal',
        'rusak'        => 'Rusak',
        'instal_ulang' => 'Instal Ulang',
        'tidak_ada'    => 'Tidak Ada',
        default        => ucfirst((string)$val),
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
<title>Detail Riwayat <?= htmlspecialchars($labelPeriode); ?> – LabSystem</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --bg: #F7F7F5; --surface: #FFFFFF; --border: #E8E8E3; --text: #18181B;
    --muted: #8C8C8A; --accent: #1A1A1A; --red: #DC2626; --red-soft: #FEF2F2;
    --green: #16A34A; --green-soft: #F0FDF4; --blue: #2563EB; --blue-soft: #EFF6FF;
    --amber: #F59E0B; --amber-soft: #FFFBEB; --gray-soft: #F1F1EF;
    --sidebar-w: 228px; --radius: 10px;
}
body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); font-size: 14px; line-height: 1.5; }

.sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: var(--sidebar-w); background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; padding: 24px 16px; z-index: 1000; transition: transform .25s ease; }
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

.topbar { display: none; position: sticky; top: 0; z-index: 990; background: var(--surface); border-bottom: 1px solid var(--border); padding: 12px 16px; align-items: center; justify-content: space-between; }
.topbar-title { font-size: 14px; font-weight: 600; }
.btn-icon { width: 36px; height: 36px; border: 1px solid var(--border); background: var(--surface); border-radius: 8px; cursor: pointer; display: grid; place-items: center; font-size: 16px; color: var(--text); transition: background .15s; }
.btn-icon:hover { background: var(--bg); }

.main { margin-left: var(--sidebar-w); min-height: 100vh; padding: 32px 36px; }
.back-link { display: inline-flex; align-items: center; gap: 6px; color: var(--muted); text-decoration: none; font-size: 13px; margin-bottom: 14px; }
.back-link:hover { color: var(--text); }

.page-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 22px; flex-wrap: wrap; }
.page-header-left h1 { font-size: 20px; font-weight: 600; letter-spacing: -.3px; margin-bottom: 2px; }
.page-header-left p { font-size: 13px; color: var(--muted); }

.summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px; }
.summary-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px 18px; display: flex; align-items: center; gap: 12px; }
.summary-icon { width: 38px; height: 38px; border-radius: 9px; display: grid; place-items: center; font-size: 16px; flex-shrink: 0; }
.summary-icon.kursi { background: var(--blue-soft); color: var(--blue); }
.summary-icon.meja { background: var(--amber-soft); color: var(--amber); }
.summary-icon.ac { background: var(--green-soft); color: var(--green); }
.summary-icon.info { background: var(--gray-soft); color: var(--muted); }
.summary-info span { display: block; font-size: 11.5px; color: var(--muted); margin-bottom: 1px; }
.summary-info strong { font-size: 16px; font-weight: 600; }

.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; margin-bottom: 20px; }
.card-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--border); gap: 12px; flex-wrap: wrap; }
.card-header-left h2 { font-size: 13.5px; font-weight: 600; color: var(--text); margin-bottom: 1px; }
.card-header-left span { font-size: 12px; color: var(--muted); }
.count-chip { display: inline-flex; align-items: center; padding: 2px 8px; background: var(--bg); border: 1px solid var(--border); border-radius: 100px; font-size: 11.5px; font-weight: 600; color: var(--muted); font-family: 'DM Mono', monospace; }

.btn-primary { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: var(--accent); color: #fff; font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500; border: none; border-radius: 7px; text-decoration: none; cursor: pointer; transition: opacity .15s; white-space: nowrap; }
.btn-primary:hover { opacity: .85; color: #fff; }

.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
thead th { padding: 11px 18px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); background: var(--bg); border-bottom: 1px solid var(--border); white-space: nowrap; }
tbody tr { border-bottom: 1px solid var(--border); }
tbody tr:last-child { border-bottom: none; }
tbody td { padding: 12px 18px; font-size: 13px; color: var(--text); vertical-align: middle; }
.row-num { font-family: 'DM Mono', monospace; font-size: 12px; color: var(--muted); width: 44px; }

.badge-cond { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 100px; font-size: 11.5px; font-weight: 500; }
.cond-normal    { background: var(--green-soft); color: var(--green); }
.cond-rusak     { background: var(--red-soft);   color: var(--red); }
.cond-instal    { background: var(--amber-soft); color: var(--amber); }
.cond-tidak-ada { background: var(--gray-soft);  color: var(--muted); }

.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.3); z-index: 999; }

@media (max-width: 1100px) { .summary-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.show { transform: translateX(0); }
    .sidebar-overlay.show { display: block; }
    .topbar { display: flex; }
    .main { margin-left: 0; padding: 16px; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .page-header > * { width: 100%; }
}
@media (max-width: 480px) { .summary-grid { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<div class="sidebar-overlay" id="overlay"></div>

<header class="topbar">
    <button class="btn-icon" id="toggleSidebar"><i class="bi bi-list"></i></button>
    <span class="topbar-title">Detail Riwayat</span>
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
        <li class="nav-item"><a class="nav-link danger" href="../../auth/logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
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

    <a href="riwayat_inventaris.php?id_lab=<?= urlencode($id_lab); ?>" class="back-link">
        <i class="bi bi-arrow-left"></i> Kembali ke Riwayat Inventaris
    </a>

    <div class="page-header">
        <div class="page-header-left">
            <h1><?= htmlspecialchars($periode['nama_lab']); ?> — <?= htmlspecialchars($labelPeriode); ?></h1>
            <p>Snapshot kondisi inventaris yang dicatat pada bulan ini (data historis, tidak dapat diedit)</p>
        </div>
        <a href="export_riwayat_inventaris.php?id_periode=<?= $id_periode; ?>" class="btn-primary">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
        </a>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-icon kursi"><i class="bi bi-menu-app"></i></div>
            <div class="summary-info"><span>Jumlah Kursi</span><strong><?= (int)$periode['jumlah_kursi']; ?></strong></div>
        </div>
        <div class="summary-card">
            <div class="summary-icon meja"><i class="bi bi-display"></i></div>
            <div class="summary-info"><span>Jumlah Meja</span><strong><?= (int)$periode['jumlah_meja']; ?></strong></div>
        </div>
        <div class="summary-card">
            <div class="summary-icon ac"><i class="bi bi-snow2"></i></div>
            <div class="summary-info"><span>Jumlah AC</span><strong><?= (int)$periode['jumlah_ac']; ?></strong></div>
        </div>
        <div class="summary-card">
            <div class="summary-icon info"><i class="bi bi-person-check"></i></div>
            <div class="summary-info"><span>Dicatat Oleh</span><strong style="font-size:13px;"><?= htmlspecialchars($periode['dicatat_oleh'] ?? '-'); ?></strong></div>
        </div>
    </div>

    <?php if (!empty($periode['keterangan'])): ?>
    <div class="card">
        <div class="card-body" style="padding:16px 20px;font-size:13px;color:var(--muted);">
            <strong style="color:var(--text);">Keterangan: </strong><?= htmlspecialchars($periode['keterangan']); ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <h2>Inventaris AC</h2>
                <span>Kondisi AC pada periode ini</span>
            </div>
            <span class="count-chip"><?= mysqli_num_rows($acQuery); ?> unit</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th style="width:44px">#</th><th>Unit AC</th><th>Kondisi</th></tr></thead>
                <tbody>
                <?php $no=1; if (mysqli_num_rows($acQuery) > 0): while ($ac = mysqli_fetch_assoc($acQuery)): ?>
                    <tr>
                        <td class="row-num"><?= $no++; ?></td>
                        <td>AC Unit #<?= (int)$ac['nomor_ac']; ?></td>
                        <td><span class="badge-cond <?= kondisiClass($ac['kondisi']); ?>"><?= kondisiLabel($ac['kondisi']); ?></span></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:24px;">Tidak ada data AC pada periode ini.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <h2>Inventaris Meja &amp; Perangkat</h2>
                <span>Kondisi CPU, keyboard, mouse, monitor, kursi pada periode ini</span>
            </div>
            <span class="count-chip"><?= mysqli_num_rows($mejaQuery); ?> meja</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:44px">#</th><th>CPU</th><th>Keyboard</th><th>Mouse</th><th>Monitor</th><th>Kursi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($m = mysqli_fetch_assoc($mejaQuery)): ?>
                    <tr>
                        <td class="row-num"><?= (int)$m['nomor_meja']; ?></td>
                        <td><span class="badge-cond <?= kondisiClass($m['cpu_kondisi']); ?>"><?= kondisiLabel($m['cpu_kondisi']); ?></span></td>
                        <td><span class="badge-cond <?= kondisiClass($m['keyboard_kondisi']); ?>"><?= kondisiLabel($m['keyboard_kondisi']); ?></span></td>
                        <td><span class="badge-cond <?= kondisiClass($m['mouse_kondisi']); ?>"><?= kondisiLabel($m['mouse_kondisi']); ?></span></td>
                        <td><span class="badge-cond <?= kondisiClass($m['monitor_kondisi']); ?>"><?= kondisiLabel($m['monitor_kondisi']); ?></span></td>
                        <td><span class="badge-cond <?= kondisiClass($m['kursi_kondisi']); ?>"><?= kondisiLabel($m['kursi_kondisi']); ?></span></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
const sidebar   = document.getElementById('sidebar');
const overlay   = document.getElementById('overlay');
const toggleBtn = document.getElementById('toggleSidebar');
toggleBtn?.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
overlay.addEventListener('click', () => { sidebar.classList.remove('show'); overlay.classList.remove('show'); });
</script>

</body>
</html>