<?php
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

$id_lab = $_GET['id_lab'] ?? null;
if (!$id_lab) { header('Location: data_lab.php'); exit; }
$id_lab = mysqli_real_escape_string($koneksi, $id_lab);

$labRes = mysqli_query($koneksi, "SELECT * FROM data_lab WHERE id_lab = '$id_lab'");
$lab = mysqli_fetch_assoc($labRes);
if (!$lab) { header('Location: data_lab.php'); exit; }

$namaBulan = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
    7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];

$periodeQuery = mysqli_query($koneksi, "SELECT * FROM periode_inventaris
    WHERE id_lab = '$id_lab' ORDER BY tahun DESC, bulan DESC");

$bulanSekarang = (int) date('n');
$tahunSekarang = (int) date('Y');

$tersimpan = isset($_GET['tersimpan']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Riwayat Inventaris <?= htmlspecialchars($lab['nama_lab']); ?> – LabSystem</title>
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
    --amber: #F59E0B; --amber-soft: #FFFBEB; --sidebar-w: 228px; --radius: 10px;
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

.alert-success {
    background: var(--green-soft); color: var(--green); border: 1px solid #bbf0cc;
    padding: 10px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 18px;
    display: flex; align-items: center; gap: 8px;
}

.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; margin-bottom: 20px; }
.card-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--border); gap: 12px; flex-wrap: wrap; }
.card-header-left h2 { font-size: 13.5px; font-weight: 600; color: var(--text); margin-bottom: 1px; }
.card-header-left span { font-size: 12px; color: var(--muted); }
.card-body { padding: 20px; }
.count-chip { display: inline-flex; align-items: center; padding: 2px 8px; background: var(--bg); border: 1px solid var(--border); border-radius: 100px; font-size: 11.5px; font-weight: 600; color: var(--muted); font-family: 'DM Mono', monospace; }

.btn-primary { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: var(--accent); color: #fff; font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500; border: none; border-radius: 7px; text-decoration: none; cursor: pointer; transition: opacity .15s; white-space: nowrap; }
.btn-primary:hover { opacity: .85; color: #fff; }

.form-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 12px; font-weight: 600; color: var(--muted); }
.form-control { padding: 9px 12px; border: 1px solid var(--border); border-radius: 7px; font-family: 'DM Sans', sans-serif; font-size: 13.5px; color: var(--text); outline: none; background: var(--surface); min-width: 140px; }
.form-control:focus { border-color: var(--accent); }

.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
thead th { padding: 11px 18px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); background: var(--bg); border-bottom: 1px solid var(--border); white-space: nowrap; }
tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #FAFAF8; }
tbody td { padding: 12px 18px; font-size: 13px; color: var(--text); vertical-align: middle; }

.periode-badge {
    display: inline-flex; align-items: center; gap: 6px; font-weight: 600; font-size: 13px;
}
.periode-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--blue); }

.action-buttons { display: flex; align-items: center; gap: 6px; }
.btn-action { width: 30px; height: 30px; display: grid; place-items: center; border-radius: 7px; border: 1px solid var(--border); background: var(--surface); color: var(--muted); font-size: 13px; text-decoration: none; cursor: pointer; transition: background .15s, color .15s, border-color .15s; }
.btn-action.btn-view:hover { background: var(--blue-soft); color: var(--blue); border-color: var(--blue); }
.btn-action.btn-export:hover { background: var(--green-soft); color: var(--green); border-color: var(--green); }
.btn-action.btn-delete:hover { background: var(--red-soft); color: var(--red); border-color: var(--red); }

.empty-state { text-align: center; padding: 48px 20px; color: var(--muted); }
.empty-state .empty-icon { width: 52px; height: 52px; background: var(--bg); border-radius: 50%; display: grid; place-items: center; margin: 0 auto 14px; font-size: 22px; color: var(--muted); }
.empty-state p { font-size: 13px; }

.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.3); z-index: 999; }

@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.show { transform: translateX(0); }
    .sidebar-overlay.show { display: block; }
    .topbar { display: flex; }
    .main { margin-left: 0; padding: 16px; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .page-header > * { width: 100%; }
    .form-control { min-width: 0; flex: 1; }
}
</style>
</head>
<body>

<div class="sidebar-overlay" id="overlay"></div>

<header class="topbar">
    <button class="btn-icon" id="toggleSidebar"><i class="bi bi-list"></i></button>
    <span class="topbar-title">Riwayat Inventaris</span>
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
        <li class="nav-item"><a class="nav-link active" href="data_lab.php"><i class="bi bi-building-fill"></i> Laboratorium</a></li>
        <li class="nav-item"><a class="nav-link" href="data_mhs.php"><i class="bi bi-people-fill"></i> Mahasiswa</a></li>
    </ul>
    <p class="nav-section">Peminjaman</p>
    <ul style="list-style:none;padding:0;margin:0">
        <li class="nav-item"><a class="nav-link" href="riwayat_pinjam.php"><i class="bi bi-clock-history"></i> Ongoing</a></li>
        <li class="nav-item"><a class="nav-link" href="arsip_peminjaman.php"><i class="bi bi-archive-fill"></i> Arsip</a></li>
    </ul>
    <div style="flex:1"></div>
    <ul style="list-style:none;padding:0;margin:0 0 12px">
        <li class="nav-item"><a class="nav-link danger" href="logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
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

    <a href="inventaris_lab.php?id_lab=<?= urlencode($id_lab); ?>" class="back-link">
        <i class="bi bi-arrow-left"></i> Kembali ke Inventaris <?= htmlspecialchars($lab['nama_lab']); ?>
    </a>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Riwayat Inventaris Bulanan — <?= htmlspecialchars($lab['nama_lab']); ?></h1>
            <p>Daftar pencatatan inventaris per bulan yang sudah dilakukan</p>
        </div>
        <a href="export_inventaris.php?id_lab=<?= urlencode($id_lab); ?>" class="btn-primary">
            <i class="bi bi-file-earmark-excel"></i> Export Kondisi Saat Ini
        </a>
    </div>

    <?php if ($tersimpan): ?>
    <div class="alert-success"><i class="bi bi-check-circle-fill"></i> Inventaris bulan ini berhasil dicatat.</div>
    <?php endif; ?>

    <!-- Form catat periode baru -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <h2>Catat Inventaris Bulan Ini</h2>
                <span>Simpan snapshot kondisi AC &amp; meja saat ini sebagai catatan resmi bulan tersebut</span>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="simpan_periode.php">
                <input type="hidden" name="id_lab" value="<?= htmlspecialchars($id_lab); ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>Bulan</label>
                        <select name="bulan" class="form-control">
                            <?php foreach ($namaBulan as $num => $nama): ?>
                            <option value="<?= $num; ?>" <?= $num === $bulanSekarang ? 'selected' : ''; ?>><?= $nama; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tahun</label>
                        <select name="tahun" class="form-control">
                            <?php for ($y = $tahunSekarang + 1; $y >= $tahunSekarang - 4; $y--): ?>
                            <option value="<?= $y; ?>" <?= $y === $tahunSekarang ? 'selected' : ''; ?>><?= $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;min-width:180px;">
                        <label>Keterangan (opsional)</label>
                        <input type="text" name="keterangan" class="form-control" style="width:100%;" placeholder="Catatan pemeriksaan bulan ini...">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary" style="padding:9px 16px;"
                            onclick="return confirm('Simpan kondisi AC & meja saat ini sebagai catatan inventaris untuk periode yang dipilih? Jika periode tersebut sudah pernah dicatat, data lama akan diperbarui.')">
                            <i class="bi bi-save"></i> Simpan Catatan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar periode tersimpan -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <h2>Daftar Periode Tercatat</h2>
                <span>Klik ikon mata untuk melihat detail, atau export untuk mengunduh Excel</span>
            </div>
            <span class="count-chip"><?= mysqli_num_rows($periodeQuery); ?> periode</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Kursi</th>
                        <th>Meja</th>
                        <th>AC</th>
                        <th>Dicatat Oleh</th>
                        <th>Tanggal Catat</th>
                        <th style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($periodeQuery) > 0): ?>
                    <?php while ($p = mysqli_fetch_assoc($periodeQuery)): ?>
                    <tr>
                        <td>
                            <span class="periode-badge"><span class="dot"></span><?= $namaBulan[(int)$p['bulan']] . ' ' . $p['tahun']; ?></span>
                        </td>
                        <td><?= (int) $p['jumlah_kursi']; ?></td>
                        <td><?= (int) $p['jumlah_meja']; ?></td>
                        <td><?= (int) $p['jumlah_ac']; ?></td>
                        <td><?= htmlspecialchars($p['dicatat_oleh'] ?? '-'); ?></td>
                        <td><?= date('d-m-Y H:i', strtotime($p['tanggal_catat'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="detail_riwayat_inventaris.php?id_periode=<?= $p['id_periode']; ?>" class="btn-action btn-view" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="export_riwayat_inventaris.php?id_periode=<?= $p['id_periode']; ?>" class="btn-action btn-export" title="Export Excel">
                                    <i class="bi bi-file-earmark-excel"></i>
                                </a>
                                <a href="hapus_periode_inventaris.php?id_periode=<?= $p['id_periode']; ?>&id_lab=<?= urlencode($id_lab); ?>"
                                   class="btn-action btn-delete" title="Hapus"
                                   onclick="return confirm('Hapus catatan periode <?= $namaBulan[(int)$p['bulan']] . ' ' . $p['tahun']; ?>?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-calendar-x"></i></div>
                                <p>Belum ada catatan inventaris bulanan untuk lab ini.</p>
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
toggleBtn?.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
overlay.addEventListener('click', () => { sidebar.classList.remove('show'); overlay.classList.remove('show'); });
</script>

</body>
</html>