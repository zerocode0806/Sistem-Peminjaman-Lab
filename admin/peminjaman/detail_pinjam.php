<?php
session_start();
require_once __DIR__ . '/../../config/koneksi.php';

/* ===============================
   CEK LOGIN ADMIN
================================ */
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}

/* ===============================
   VALIDASI ID PINJAM
================================ */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ../dashboard.php');
    exit;
}

/* ===============================
   AMBIL DATA PINJAM + MAHASISWA
================================ */
$stmt = mysqli_prepare($koneksi, "
    SELECT
        p.id_data,
        p.nim,
        p.jenis,
        p.nama_lab,
        p.kursi,
        p.id_barang,
        p.nama_barang,
        p.jumlah,
        p.tanggal,
        p.jam_mulai,
        p.jam_selesai,
        p.status,

        m.nama AS nama_mhs,
        m.no_telepon,
        m.alamat
    FROM data_pinjam p
    JOIN mahasiswa m ON p.nim = m.nim
    WHERE p.id_data = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_stmt_get_result($stmt)->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); window.location='../dashboard.php';</script>";
    exit;
}

// Untuk data lama sebelum kolom 'jenis' ditambahkan, anggap sebagai peminjaman lab
$jenis = $data['jenis'] ?: 'lab';

/* ===============================
   UPDATE STATUS (WORKFLOW) — TANDAI SELESAI
================================ */
if (isset($_POST['update_status'])) {

    // Ambil ulang status terbaru dari database (hindari manipulasi)
    $stmtCek = mysqli_prepare($koneksi, "SELECT status FROM data_pinjam WHERE id_data = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtCek, "i", $id);
    mysqli_stmt_execute($stmtCek);
    $row = mysqli_stmt_get_result($stmtCek)->fetch_assoc();

    if ($row && $row['status'] === 'disetujui') {

        mysqli_begin_transaction($koneksi);

        try {
            // 1. Update status jadi selesai
            $stmtUpdate = mysqli_prepare($koneksi, "UPDATE data_pinjam SET status = 'selesai' WHERE id_data = ?");
            mysqli_stmt_bind_param($stmtUpdate, "i", $id);
            if (!mysqli_stmt_execute($stmtUpdate)) {
                throw new Exception("Gagal memperbarui status peminjaman.");
            }

            // 2. Kembalikan stok sesuai jenis peminjaman
            if ($jenis === 'barang' && $data['id_barang']) {
                $jumlahKembali = (int) $data['jumlah'];
                $stmtRestore = mysqli_prepare($koneksi, "UPDATE data_barang SET stok = stok + ? WHERE id_barang = ?");
                mysqli_stmt_bind_param($stmtRestore, "ii", $jumlahKembali, $data['id_barang']);
                if (!mysqli_stmt_execute($stmtRestore)) {
                    throw new Exception("Gagal mengembalikan stok barang.");
                }
            } elseif ($data['nama_lab']) {
                $stmtRestore = mysqli_prepare($koneksi, "UPDATE data_lab SET stok = stok + 1 WHERE nama_lab = ?");
                mysqli_stmt_bind_param($stmtRestore, "s", $data['nama_lab']);
                if (!mysqli_stmt_execute($stmtRestore)) {
                    throw new Exception("Gagal mengembalikan stok laboratorium.");
                }
            }

            mysqli_commit($koneksi);
            header("Location: checkout_pinjam.php?id=$id&msg=success");
            exit;

        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Checkout Peminjaman – LabSystem</title>

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
    --warn:       #D97706;
    --warn-soft:  #FFFBEB;
    --blue:       #2563EB;
    --blue-soft:  #EFF4FF;
    --cyan:       #0891B2;
    --cyan-soft:  #ECFEFF;
    --violet:     #7C3AED;
    --violet-soft:#F5F3FF;
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
    max-width: 880px;
    margin: 0 auto;
    padding: 36px 24px 60px;
}

/* ── TOP NAV ── */
.top-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    gap: 12px;
    flex-wrap: wrap;
}

.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--muted);
}

.breadcrumb-nav a { color: var(--muted); text-decoration: none; transition: color .15s; }
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
    flex-shrink: 0;
}

.btn-back:hover { background: var(--bg); color: var(--text); }

/* ── ALERTS ── */
.alert-success {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--green-soft);
    border: 1px solid #BBF7D0;
    border-radius: var(--radius);
    padding: 12px 16px;
    margin-bottom: 20px;
    font-size: 13.5px;
    color: var(--green);
    font-weight: 500;
}
.alert-success i { font-size: 16px; flex-shrink: 0; }

.alert-error {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--red-soft);
    border: 1px solid #FECACA;
    border-radius: var(--radius);
    padding: 12px 16px;
    margin-bottom: 20px;
    font-size: 13.5px;
    color: var(--red);
    font-weight: 500;
}
.alert-error i { font-size: 16px; flex-shrink: 0; }

/* ── PAGE TITLE ROW ── */
.page-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.page-title-left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.page-title-row h1 { font-size: 18px; font-weight: 600; letter-spacing: -.3px; }

/* ── BADGES ── */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 100px;
    font-size: 12.5px;
    font-weight: 500;
    white-space: nowrap;
}

.badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

.badge-menunggu  { background: var(--warn-soft);  color: var(--warn);  }
.badge-menunggu::before  { background: var(--warn); }
.badge-disetujui { background: var(--green-soft); color: var(--green); }
.badge-disetujui::before { background: var(--green); }
.badge-ditolak   { background: var(--red-soft);   color: var(--red);   }
.badge-ditolak::before   { background: var(--red); }
.badge-selesai   { background: var(--cyan-soft);  color: var(--cyan);  }
.badge-selesai::before   { background: var(--cyan); }
.badge-default   { background: var(--bg); color: var(--muted); border: 1px solid var(--border); }
.badge-default::before   { background: var(--muted); }

/* Jenis badge (Lab / Barang) */
.badge-jenis {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 100px;
    font-size: 12.5px;
    font-weight: 500;
    white-space: nowrap;
}
.badge-jenis i { font-size: 12px; }
.badge-jenis-lab    { background: var(--blue-soft);   color: var(--blue); }
.badge-jenis-barang { background: var(--violet-soft); color: var(--violet); }

/* ── DETAIL GRID ── */
.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

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

.card-header h2 { font-size: 13.5px; font-weight: 600; color: var(--text); }

/* ── INFO ROWS ── */
.info-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 12px 20px;
    border-bottom: 1px solid var(--border);
}

.info-item:last-child { border-bottom: none; }

.info-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--muted);
}

.info-value { font-size: 13.5px; font-weight: 500; color: var(--text); }
.info-value.mono { font-family: 'DM Mono', monospace; font-size: 13px; }

.time-range {
    font-family: 'DM Mono', monospace;
    font-size: 12.5px;
    color: var(--muted);
    background: var(--bg);
    border: 1px solid var(--border);
    padding: 3px 8px;
    border-radius: 5px;
    display: inline-block;
}

/* ── CHECKOUT CARD ── */
.checkout-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.checkout-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    border-bottom: 1px solid var(--border);
}

.checkout-card-header-icon {
    width: 28px; height: 28px;
    background: var(--bg);
    border-radius: 6px;
    display: grid;
    place-items: center;
    font-size: 13px;
    color: var(--muted);
    flex-shrink: 0;
}

.checkout-card-header h2 { font-size: 13.5px; font-weight: 600; color: var(--text); }
.checkout-card-body { padding: 24px; }

/* ── CHECKOUT CONFIRM BOX ── */
.confirm-box {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 20px;
    background: var(--green-soft);
    border: 1px solid #BBF7D0;
    border-radius: 8px;
    margin-bottom: 20px;
}

.confirm-box-icon {
    width: 40px; height: 40px;
    background: var(--green);
    border-radius: 50%;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    font-size: 18px;
    color: #fff;
}

.confirm-box-text strong {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--green);
    margin-bottom: 3px;
}

.confirm-box-text p { font-size: 13px; color: #15803D; line-height: 1.5; }

/* ── CHECKOUT SUMMARY ── */
.checkout-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}

.summary-item {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.summary-item-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--muted);
}

.summary-item-value { font-size: 13.5px; font-weight: 600; color: var(--text); }
.summary-item-value.mono { font-family: 'DM Mono', monospace; font-size: 13px; }

/* ── FORM ACTIONS ── */
.checkout-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

.btn-checkout {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 20px;
    background: var(--green);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 13.5px;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: opacity .15s;
    white-space: nowrap;
}

.btn-checkout:hover { opacity: .88; }

/* ── LOCKED STATE ── */
.status-locked {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 13px;
    color: var(--muted);
}
.status-locked i { font-size: 15px; flex-shrink: 0; }

/* ── WORKFLOW BAR ── */
.workflow { display: flex; align-items: center; margin-bottom: 24px; }

.workflow-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    flex: 1;
    position: relative;
}

.workflow-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 14px;
    left: 50%;
    width: 100%;
    height: 2px;
    background: var(--border);
    z-index: 0;
}

.workflow-step.done:not(:last-child)::after { background: var(--green); }

.step-dot {
    width: 28px; height: 28px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 13px;
    border: 2px solid var(--border);
    background: var(--surface);
    color: var(--muted);
    position: relative;
    z-index: 1;
}

.workflow-step.done   .step-dot { background: var(--green);  border-color: var(--green);  color: #fff; }
.workflow-step.active .step-dot { background: var(--cyan);   border-color: var(--cyan);   color: #fff; box-shadow: 0 0 0 4px var(--cyan-soft); }

.step-label { font-size: 11px; font-weight: 500; color: var(--muted); white-space: nowrap; text-align: center; }
.workflow-step.done   .step-label,
.workflow-step.active .step-label { color: var(--text); font-weight: 600; }

/* ── RESPONSIVE ── */
@media (max-width: 640px) {
    .page-wrap { padding: 16px 16px 48px; }
    .detail-grid { grid-template-columns: 1fr; }
    .checkout-summary { grid-template-columns: 1fr 1fr; }
    .top-nav { flex-direction: column; align-items: flex-start; }
    .btn-checkout { width: 100%; justify-content: center; }
    .workflow .step-label { font-size: 10px; }
}

@media (max-width: 400px) {
    .checkout-summary { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<div class="page-wrap">

    <!-- Top Nav -->
    <div class="top-nav">
        <nav class="breadcrumb-nav">
            <a href="../dashboard.php">Dashboard</a>
            <i class="bi bi-chevron-right"></i>
            <a href="riwayat_pinjam.php">Riwayat</a>
            <i class="bi bi-chevron-right"></i>
            <span class="current">Checkout #<?= $id ?></span>
        </nav>
        <a href="arsip_peminjaman.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert-success">
        <i class="bi bi-check-circle-fill"></i>
        Peminjaman berhasil ditandai selesai. Stok <?= $jenis === 'barang' ? 'barang' : 'laboratorium' ?> telah dikembalikan.
    </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
    <div class="alert-error">
        <i class="bi bi-exclamation-circle-fill"></i>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <!-- Page Title + Jenis + Status -->
    <div class="page-title-row">
        <div class="page-title-left">
            <h1>Checkout Peminjaman</h1>
            <?php if ($jenis === 'barang'): ?>
                <span class="badge-jenis badge-jenis-barang"><i class="bi bi-box-seam-fill"></i> Barang / Alat</span>
            <?php else: ?>
                <span class="badge-jenis badge-jenis-lab"><i class="bi bi-building"></i> Ruang Lab</span>
            <?php endif; ?>
        </div>
        <?php
        $badgeClass = match($data['status']) {
            'menunggu'  => 'badge-menunggu',
            'disetujui' => 'badge-disetujui',
            'ditolak'   => 'badge-ditolak',
            'selesai'   => 'badge-selesai',
            default     => 'badge-default'
        };
        ?>
        <span class="badge <?= $badgeClass ?>">
            <?= ucfirst($data['status']) ?>
        </span>
    </div>

    <!-- Info Cards -->
    <div class="detail-grid">

        <!-- Mahasiswa -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon"><i class="bi bi-person"></i></div>
                <h2>Data Mahasiswa</h2>
            </div>
            <div class="info-item">
                <span class="info-label">Nama</span>
                <span class="info-value"><?= htmlspecialchars($data['nama_mhs']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">NIM</span>
                <span class="info-value mono"><?= htmlspecialchars($data['nim']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">No. Telepon</span>
                <span class="info-value mono"><?= htmlspecialchars($data['no_telepon']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Alamat</span>
                <span class="info-value"><?= htmlspecialchars($data['alamat']) ?></span>
            </div>
        </div>

        <!-- Peminjaman -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon">
                    <i class="<?= $jenis === 'barang' ? 'bi bi-box-seam' : 'bi bi-building' ?>"></i>
                </div>
                <h2><?= $jenis === 'barang' ? 'Detail Barang Dipinjam' : 'Detail Peminjaman' ?></h2>
            </div>

            <?php if ($jenis === 'barang'): ?>

                <div class="info-item">
                    <span class="info-label">Barang</span>
                    <span class="info-value"><?= htmlspecialchars($data['nama_barang'] ?? '—') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Jumlah</span>
                    <span class="info-value"><?= (int)($data['jumlah'] ?? 0) ?> unit</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Lab Asal Barang</span>
                    <span class="info-value"><?= htmlspecialchars($data['nama_lab']) ?></span>
                </div>

            <?php else: ?>

                <div class="info-item">
                    <span class="info-label">Laboratorium</span>
                    <span class="info-value"><?= htmlspecialchars($data['nama_lab']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Kursi</span>
                    <span class="info-value"><?= htmlspecialchars($data['kursi']) ?></span>
                </div>

            <?php endif; ?>

            <div class="info-item">
                <span class="info-label">Tanggal</span>
                <span class="info-value mono"><?= date('d M Y', strtotime($data['tanggal'])) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Waktu</span>
                <span class="info-value">
                    <span class="time-range">
                        <?= substr($data['jam_mulai'], 0, 5) ?> – <?= substr($data['jam_selesai'], 0, 5) ?>
                    </span>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">ID Peminjaman</span>
                <span class="info-value mono">#<?= $data['id_data'] ?></span>
            </div>
        </div>

    </div>

    <!-- Checkout Action Card -->
    <div class="checkout-card">
        <div class="checkout-card-header">
            <div class="checkout-card-header-icon"><i class="bi bi-box-arrow-in-down"></i></div>
            <h2><?= $jenis === 'barang' ? 'Pengembalian Barang' : 'Pengembalian Laboratorium' ?></h2>
        </div>
        <div class="checkout-card-body">

            <!-- Workflow -->
            <div class="workflow">
                <div class="workflow-step done">
                    <div class="step-dot"><i class="bi bi-check-lg"></i></div>
                    <span class="step-label">Menunggu</span>
                </div>
                <div class="workflow-step done">
                    <div class="step-dot"><i class="bi bi-check-lg"></i></div>
                    <span class="step-label">Disetujui</span>
                </div>
                <div class="workflow-step <?= $data['status'] === 'selesai' ? 'active' : '' ?>">
                    <div class="step-dot"><i class="bi bi-flag-fill"></i></div>
                    <span class="step-label">Selesai</span>
                </div>
            </div>

            <?php if ($data['status'] == 'disetujui'): ?>

            <!-- Confirm box -->
            <div class="confirm-box">
                <div class="confirm-box-icon"><i class="bi bi-arrow-return-left"></i></div>
                <div class="confirm-box-text">
                    <strong>Konfirmasi Pengembalian</strong>
                    <?php if ($jenis === 'barang'): ?>
                        <p>Tandai peminjaman ini sebagai selesai untuk mengembalikan stok barang
                           <strong><?= htmlspecialchars($data['nama_barang'] ?? '') ?></strong>
                           sebanyak <strong><?= (int)($data['jumlah'] ?? 0) ?> unit</strong> dan menutup catatan peminjaman.</p>
                    <?php else: ?>
                        <p>Tandai peminjaman ini sebagai selesai untuk mengembalikan stok laboratorium
                           <strong><?= htmlspecialchars($data['nama_lab']) ?></strong> dan menutup catatan peminjaman.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Summary -->
            <div class="checkout-summary">
                <?php if ($jenis === 'barang'): ?>
                    <div class="summary-item">
                        <span class="summary-item-label">Barang</span>
                        <span class="summary-item-value"><?= htmlspecialchars($data['nama_barang'] ?? '—') ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-item-label">Jumlah</span>
                        <span class="summary-item-value mono"><?= (int)($data['jumlah'] ?? 0) ?> unit</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-item-label">Tanggal</span>
                        <span class="summary-item-value mono"><?= date('d M Y', strtotime($data['tanggal'])) ?></span>
                    </div>
                <?php else: ?>
                    <div class="summary-item">
                        <span class="summary-item-label">Lab</span>
                        <span class="summary-item-value"><?= htmlspecialchars($data['nama_lab']) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-item-label">Tanggal</span>
                        <span class="summary-item-value mono"><?= date('d M Y', strtotime($data['tanggal'])) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-item-label">Durasi</span>
                        <span class="summary-item-value mono"><?= substr($data['jam_mulai'], 0, 5) ?> – <?= substr($data['jam_selesai'], 0, 5) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submit -->
            <form method="POST" class="checkout-actions">
                <button type="submit" name="update_status"
                        class="btn-checkout"
                        onclick="return confirm('Yakin ingin menyelesaikan peminjaman ini?')">
                    <i class="bi bi-check2-circle"></i> Tandai Selesai &amp; Kembalikan Stok
                </button>
            </form>

            <?php else: ?>
            <div class="status-locked">
                <i class="bi bi-lock-fill"></i>
                <span>
                    Peminjaman ini berstatus <strong><?= ucfirst($data['status']) ?></strong> dan tidak dapat diubah menjadi selesai.
                </span>
            </div>
            <?php endif; ?>

        </div>
    </div>

</div>

</body>
</html>