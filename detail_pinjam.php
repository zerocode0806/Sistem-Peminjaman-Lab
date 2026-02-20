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
   VALIDASI ID PINJAM
================================ */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

/* ===============================
   AMBIL DATA PINJAM + MAHASISWA
================================ */
$query = mysqli_query($koneksi, "
    SELECT 
        p.id_data,
        p.nim,
        p.nama_lab,
        p.tanggal,
        p.jam_mulai,
        p.jam_selesai,
        p.status,

        m.nama AS nama_mhs,
        m.no_telepon,
        m.alamat
    FROM data_pinjam p
    JOIN mahasiswa m ON p.nim = m.nim
    WHERE p.id_data = '$id'
    LIMIT 1
");

$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); window.location='dashboard.php';</script>";
    exit;
}

/* ===============================
   UPDATE STATUS (WORKFLOW)
================================ */
if (isset($_POST['update_status'])) {

    $status_baru = $_POST['status'];
    $status_lama = $data['status'];
    $allowed = [];

    // Workflow
    if ($status_lama == 'menunggu') {
        $allowed = ['disetujui', 'ditolak'];
    } elseif ($status_lama == 'disetujui') {
        $allowed = ['selesai'];
    }

    if (in_array($status_baru, $allowed)) {

        mysqli_query($koneksi, "
            UPDATE data_pinjam
            SET status = '$status_baru'
            WHERE id_data = '$id'
        ");

        echo "<script>
            window.history.back();
          </script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Detail Peminjaman – LabSystem</title>

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
    --cyan:       #0891B2;
    --cyan-soft:  #ECFEFF;
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
    cursor: pointer;
    transition: background .15s;
    flex-shrink: 0;
}

.btn-back:hover { background: var(--bg); color: var(--text); }

/* ── PAGE TITLE ROW ── */
.page-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.page-title-row h1 {
    font-size: 18px;
    font-weight: 600;
    letter-spacing: -.3px;
}

/* ── STATUS BADGE ── */
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

.badge::before {
    content: '';
    width: 6px; height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}

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

.card-header h2 {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text);
}

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

.info-value {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text);
}

.info-value.mono {
    font-family: 'DM Mono', monospace;
    font-size: 13px;
}

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

/* ── ACTION CARD ── */
.action-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.action-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    border-bottom: 1px solid var(--border);
}

.action-card-header-icon {
    width: 28px; height: 28px;
    background: var(--bg);
    border-radius: 6px;
    display: grid;
    place-items: center;
    font-size: 13px;
    color: var(--muted);
    flex-shrink: 0;
}

.action-card-header h2 {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text);
}

.action-card-body { padding: 24px; }

/* ── WORKFLOW BAR ── */
.workflow {
    display: flex;
    align-items: center;
    margin-bottom: 24px;
}

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

.workflow-step.done:not(:last-child)::after     { background: var(--green); }
.workflow-step.rejected:not(:last-child)::after { background: var(--red); }

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

.workflow-step.done     .step-dot { background: var(--green);  border-color: var(--green);  color: #fff; }
.workflow-step.current  .step-dot { background: var(--warn);   border-color: var(--warn);   color: #fff; box-shadow: 0 0 0 4px var(--warn-soft); }
.workflow-step.rejected .step-dot { background: var(--red);    border-color: var(--red);    color: #fff; }
.workflow-step.finished .step-dot { background: var(--cyan);   border-color: var(--cyan);   color: #fff; }

.step-label {
    font-size: 11px;
    font-weight: 500;
    color: var(--muted);
    white-space: nowrap;
    text-align: center;
}

.workflow-step.done     .step-label,
.workflow-step.current  .step-label,
.workflow-step.rejected .step-label,
.workflow-step.finished .step-label { color: var(--text); font-weight: 600; }

/* ── FORM ── */
.action-form {
    display: flex;
    gap: 10px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.form-group { flex: 1; min-width: 160px; }

.form-group label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--muted);
    margin-bottom: 6px;
}

select {
    width: 100%;
    padding: 9px 36px 9px 12px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13.5px;
    color: var(--text);
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238C8C8A' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    transition: border-color .15s, background .15s;
    cursor: pointer;
}

select:focus {
    border-color: var(--accent);
    background-color: var(--surface);
    box-shadow: 0 0 0 3px rgba(26,26,26,.06);
}

.btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 20px;
    background: var(--accent);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 13.5px;
    font-weight: 600;
    border: none;
    border-radius: 7px;
    cursor: pointer;
    transition: opacity .15s;
    white-space: nowrap;
    flex-shrink: 0;
}

.btn-submit:hover { opacity: .85; }

/* Context hint above form */
.action-hint {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 14px;
    background: var(--warn-soft);
    border: 1px solid #FDE68A;
    border-radius: 8px;
    margin-bottom: 18px;
    font-size: 13px;
    color: #92400E;
}

.action-hint.hint-green {
    background: var(--green-soft);
    border-color: #BBF7D0;
    color: #14532D;
}

.action-hint i { font-size: 15px; flex-shrink: 0; margin-top: 1px; }

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

/* ── RESPONSIVE ── */
@media (max-width: 640px) {
    .page-wrap { padding: 16px 16px 48px; }
    .detail-grid { grid-template-columns: 1fr; }
    .top-nav { flex-direction: column; align-items: flex-start; }
    .action-form { flex-direction: column; }
    .btn-submit { width: 100%; justify-content: center; }
    .workflow .step-label { font-size: 10px; }
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
            <span class="current">Detail Peminjaman #<?= $id ?></span>
        </nav>
        <a href="javascript:window.history.back()" class="btn-back">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Page Title + Current Status -->
    <div class="page-title-row">
        <h1>Detail Peminjaman</h1>
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
                <div class="card-header-icon"><i class="bi bi-building"></i></div>
                <h2>Detail Peminjaman</h2>
            </div>
            <div class="info-item">
                <span class="info-label">Laboratorium</span>
                <span class="info-value"><?= htmlspecialchars($data['nama_lab']) ?></span>
            </div>
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

    <!-- Action Card -->
    <div class="action-card">
        <div class="action-card-header">
            <div class="action-card-header-icon"><i class="bi bi-arrow-repeat"></i></div>
            <h2>Status Peminjaman</h2>
        </div>
        <div class="action-card-body">

            <!-- Workflow Visual -->
            <?php
            $isDitolak  = $data['status'] === 'ditolak';
            $statusOrder = ['menunggu', 'disetujui', 'selesai'];
            $currentIdx  = array_search($data['status'], $statusOrder);
            $stepMeta = [
                'menunggu'  => ['label' => 'Menunggu',  'icon' => 'bi-hourglass-split'],
                'disetujui' => ['label' => 'Disetujui', 'icon' => 'bi-check-lg'],
                'selesai'   => ['label' => 'Selesai',   'icon' => 'bi-flag-fill'],
            ];
            ?>
            <div class="workflow">
                <?php if ($isDitolak): ?>
                    <div class="workflow-step done">
                        <div class="step-dot"><i class="bi bi-check-lg"></i></div>
                        <span class="step-label">Menunggu</span>
                    </div>
                    <div class="workflow-step rejected">
                        <div class="step-dot"><i class="bi bi-x-lg"></i></div>
                        <span class="step-label">Ditolak</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($statusOrder as $idx => $step):
                        if ($idx < $currentIdx)       $cls = 'done';
                        elseif ($idx === $currentIdx) $cls = ($step === 'selesai') ? 'finished' : 'current';
                        else                          $cls = '';
                    ?>
                    <div class="workflow-step <?= $cls ?>">
                        <div class="step-dot">
                            <i class="<?= $idx < $currentIdx ? 'bi bi-check-lg' : $stepMeta[$step]['icon'] ?>"></i>
                        </div>
                        <span class="step-label"><?= $stepMeta[$step]['label'] ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Dynamic form or locked -->
            <?php if (in_array($data['status'], ['menunggu', 'disetujui'])): ?>

                <?php if ($data['status'] === 'menunggu'): ?>
                <div class="action-hint">
                    <i class="bi bi-exclamation-circle"></i>
                    Peminjaman ini sedang menunggu persetujuan. Pilih tindakan untuk memperbarui statusnya.
                </div>
                <?php else: ?>
                <div class="action-hint hint-green">
                    <i class="bi bi-info-circle"></i>
                    Peminjaman ini sudah disetujui. Tandai sebagai selesai setelah mahasiswa mengembalikan laboratorium.
                </div>
                <?php endif; ?>

                <form method="POST" class="action-form">
                    <div class="form-group">
                        <label>Pilih tindakan</label>
                        <select name="status" required>
                            <?php if ($data['status'] == 'menunggu'): ?>
                                <option value="disetujui">✓ Setujui peminjaman</option>
                                <option value="ditolak">✗ Tolak peminjaman</option>
                            <?php elseif ($data['status'] == 'disetujui'): ?>
                                <option value="selesai">✓ Tandai selesai</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn-submit">
                        <i class="bi bi-check-lg"></i> Simpan
                    </button>
                </form>

            <?php else: ?>
                <div class="status-locked">
                    <i class="bi bi-lock-fill"></i>
                    <span>Status sudah final (<strong><?= ucfirst($data['status']) ?></strong>) dan tidak dapat diubah.</span>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

</body>
</html>