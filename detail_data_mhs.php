<?php
session_start();
include 'koneksi.php';

if (empty($_GET['nim'])) {
    die("NIM tidak ditemukan di URL");
}

$nim = mysqli_real_escape_string($koneksi, trim($_GET['nim']));


/* ===============================
   DATA MAHASISWA
================================ */
$qMhs = mysqli_query($koneksi, "
    SELECT nama, no_telepon, alamat
    FROM mahasiswa
    WHERE nim = '$nim'
");
$mhs = mysqli_fetch_assoc($qMhs);

if (!$mhs) {
    die("Data mahasiswa tidak ditemukan");
}

/* ===============================
   RIWAYAT PINJAM
================================ */
$qPinjam = mysqli_query($koneksi, "
    SELECT *
    FROM data_pinjam
    WHERE nim = '$nim'
      AND status IN ('selesai', 'ditolak')
    ORDER BY tanggal DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Detail Mahasiswa – LabSystem</title>

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
    --radius:     10px;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    font-size: 14px;
    line-height: 1.5;
    min-height: 100vh;
}

/* ── PAGE SHELL ── */
.page-wrap {
    max-width: 960px;
    margin: 0 auto;
    padding: 36px 24px 56px;
}

/* ── TOP NAV ── */
.top-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
}

.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--muted);
}

.breadcrumb-nav a {
    color: var(--muted);
    text-decoration: none;
    transition: color .15s;
}

.breadcrumb-nav a:hover { color: var(--text); }

.breadcrumb-nav i { font-size: 11px; }

.breadcrumb-nav .current { color: var(--text); font-weight: 500; }

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 13px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: var(--text);
    text-decoration: none;
    transition: background .15s;
}

.btn-back:hover { background: var(--bg); color: var(--text); }

/* ── PROFILE HEADER ── */
.profile-header {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px 28px;
    display: flex;
    align-items: center;
    gap: 22px;
    margin-bottom: 20px;
}

.profile-avatar {
    width: 56px; height: 56px;
    background: var(--accent);
    border-radius: 50%;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    font-family: 'DM Mono', monospace;
    font-size: 20px;
    font-weight: 600;
    color: #fff;
}

.profile-info h1 {
    font-size: 18px;
    font-weight: 600;
    letter-spacing: -.3px;
    margin-bottom: 4px;
}

.profile-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.meta-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: var(--muted);
    background: var(--bg);
    border: 1px solid var(--border);
    padding: 3px 9px;
    border-radius: 100px;
}

.meta-pill i { font-size: 11px; }

.meta-pill.nim {
    font-family: 'DM Mono', monospace;
    font-size: 12px;
    font-weight: 500;
    color: var(--text);
}

/* ── INFO CARD ── */
.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    margin-bottom: 20px;
}

.card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    border-bottom: 1px solid var(--border);
}

.card-header-icon {
    width: 28px; height: 28px;
    background: var(--bg);
    border-radius: 6px;
    display: grid;
    place-items: center;
    font-size: 13px;
    color: var(--muted);
    flex-shrink: 0;
}

.card-header h2 {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text);
}

.card-header .count-chip {
    margin-left: auto;
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

.card-body { padding: 20px; }

/* ── INFO GRID ── */
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0;
}

.info-row {
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    border-right: 1px solid var(--border);
}

.info-row:nth-child(even) { border-right: none; }
.info-row:nth-last-child(-n+2) { border-bottom: none; }

.info-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--muted);
}

.info-value {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text);
}

.info-value.mono {
    font-family: 'DM Mono', monospace;
    font-size: 13px;
}

/* ── HISTORY GRID ── */
.history-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 12px;
    padding: 20px;
}

.history-item {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 16px;
    background: var(--bg);
    transition: box-shadow .2s, background .15s;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.history-item:hover {
    background: var(--surface);
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
}

.history-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
}

.history-lab {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text);
    line-height: 1.3;
}

/* STATUS BADGE */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 100px;
    font-size: 11px;
    font-weight: 500;
    white-space: nowrap;
    flex-shrink: 0;
}

.badge::before {
    content: '';
    width: 5px; height: 5px;
    border-radius: 50%;
    flex-shrink: 0;
}

.badge-selesai  { background: var(--green-soft); color: var(--green); }
.badge-selesai::before  { background: var(--green); }
.badge-ditolak  { background: var(--red-soft);   color: var(--red); }
.badge-ditolak::before  { background: var(--red); }
.badge-default  { background: var(--bg); color: var(--muted); border: 1px solid var(--border); }
.badge-default::before  { background: var(--muted); }

.history-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.history-date {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: var(--muted);
}

.history-date i { font-size: 11px; }

.history-time {
    font-family: 'DM Mono', monospace;
    font-size: 11.5px;
    color: var(--muted);
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 2px 8px;
    border-radius: 5px;
}

/* ── EMPTY STATE ── */
.empty-state {
    text-align: center;
    padding: 48px 20px;
    color: var(--muted);
}

.empty-icon {
    width: 48px; height: 48px;
    background: var(--bg);
    border-radius: 50%;
    display: grid;
    place-items: center;
    margin: 0 auto 12px;
    font-size: 20px;
    color: var(--muted);
    border: 1px solid var(--border);
}

.empty-state p { font-size: 13px; }

/* ── RESPONSIVE ── */
@media (max-width: 600px) {
    .page-wrap { padding: 16px 16px 40px; }
    .profile-header { flex-direction: column; align-items: flex-start; gap: 14px; }
    .info-grid { grid-template-columns: 1fr; }
    .info-row { border-right: none !important; }
    .info-row:nth-last-child(-n+2) { border-bottom: 1px solid var(--border); }
    .info-row:last-child { border-bottom: none; }
    .history-grid { grid-template-columns: 1fr; }
    .top-nav { flex-direction: column; align-items: flex-start; gap: 12px; }
}
</style>
</head>
<body>

<div class="page-wrap">

    <!-- Top Nav -->
    <div class="top-nav">
        <nav class="breadcrumb-nav">
            <a href="dashboard.php">Dashboard</a>
            <i class="bi bi-chevron-right"></i>
            <a href="data_mhs.php">Mahasiswa</a>
            <i class="bi bi-chevron-right"></i>
            <span class="current"><?= htmlspecialchars($mhs['nama']) ?></span>
        </nav>
        <a href="data_mhs.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-avatar">
            <?= strtoupper(substr($mhs['nama'], 0, 1)) ?>
        </div>
        <div class="profile-info">
            <h1><?= htmlspecialchars($mhs['nama']) ?></h1>
            <div class="profile-meta">
                <span class="meta-pill nim">
                    <i class="bi bi-hash"></i><?= htmlspecialchars($nim) ?>
                </span>
                <span class="meta-pill">
                    <i class="bi bi-telephone"></i><?= htmlspecialchars($mhs['no_telepon']) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-icon"><i class="bi bi-person"></i></div>
            <h2>Informasi Mahasiswa</h2>
        </div>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Nama Lengkap</span>
                <span class="info-value"><?= htmlspecialchars($mhs['nama']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">NIM</span>
                <span class="info-value mono"><?= htmlspecialchars($nim) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Telepon</span>
                <span class="info-value mono"><?= htmlspecialchars($mhs['no_telepon']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Alamat</span>
                <span class="info-value"><?= htmlspecialchars($mhs['alamat']) ?></span>
            </div>
        </div>
    </div>

    <!-- Riwayat Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-icon"><i class="bi bi-clock-history"></i></div>
            <h2>Riwayat Peminjaman</h2>
            <span class="count-chip"><?= mysqli_num_rows($qPinjam) ?> transaksi</span>
        </div>

        <?php if (mysqli_num_rows($qPinjam) > 0): ?>
        <div class="history-grid">
            <?php while ($row = mysqli_fetch_assoc($qPinjam)):
                $badgeClass = match($row['status']) {
                    'selesai' => 'badge-selesai',
                    'ditolak' => 'badge-ditolak',
                    default   => 'badge-default'
                };
            ?>
            <div class="history-item">
                <div class="history-top">
                    <span class="history-lab"><?= htmlspecialchars($row['nama_lab']) ?></span>
                    <span class="badge <?= $badgeClass ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </div>
                <div class="history-bottom">
                    <span class="history-date">
                        <i class="bi bi-calendar3"></i>
                        <?= date('d M Y', strtotime($row['tanggal'])) ?>
                    </span>
                    <span class="history-time">
                        <?= substr($row['jam_mulai'], 0, 5) ?> – <?= substr($row['jam_selesai'], 0, 5) ?>
                    </span>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-inbox"></i></div>
            <p>Belum ada riwayat peminjaman untuk mahasiswa ini.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>