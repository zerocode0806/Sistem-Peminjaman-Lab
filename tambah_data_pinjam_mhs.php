<?php
session_start();
include 'koneksi.php';

/* ===============================
   CEK LOGIN MAHASISWA
================================ */
if (!isset($_SESSION['mahasiswa']['nim'])) {
    header('Location: login_mhs.php');
    exit;
}

$nim = $_SESSION['mahasiswa']['nim'];

/* ===============================
   AMBIL DATA MAHASISWA
================================ */
$mhsQuery = mysqli_query($koneksi, "
    SELECT nama, no_telepon, alamat
    FROM mahasiswa
    WHERE nim = '$nim'
");

$dataMhs = mysqli_fetch_assoc($mhsQuery);
if (!$dataMhs) {
    die("Data mahasiswa tidak ditemukan.");
}

/* ===============================
   AMBIL LAB TERSEDIA
================================ */
$labQuery = mysqli_query($koneksi, "
    SELECT nama_lab
    FROM data_lab
    WHERE status = 'availabel'
    ORDER BY nama_lab ASC
");

/* ===============================
   PROSES SIMPAN PINJAM
================================ */
if (isset($_POST['simpan'])) {

    $nama_lab    = mysqli_real_escape_string($koneksi, $_POST['nama_lab']);
    $tanggal     = $_POST['tanggal'];
    $jam_mulai   = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $status      = 'menunggu';

    if ($jam_selesai <= $jam_mulai) {
        $error = "Jam selesai harus lebih besar dari jam mulai";
    } else {
        // Mulai transaksi
        mysqli_begin_transaction($koneksi);

        try {

            // 🔎 Cek stok lab
            $cekStok = mysqli_query($koneksi, "
                SELECT stok 
                FROM data_lab 
                WHERE nama_lab = '$nama_lab' 
                FOR UPDATE
            ");

            $dataLab = mysqli_fetch_assoc($cekStok);

            if (!$dataLab || $dataLab['stok'] <= 0) {
                throw new Exception("Stok laboratorium sudah habis.");
            }

            // ✅ Insert data peminjaman
            $insert = mysqli_query($koneksi, "
            INSERT INTO data_pinjam
            (nim, nama_lab, tanggal, jam_mulai, jam_selesai, status)
            VALUES
            ('$nim', '$nama_lab', '$tanggal', '$jam_mulai', '$jam_selesai', '$status')
            ");

            if (!$insert) {
                throw new Exception("Gagal menyimpan data peminjaman.");
            }

            // ➖ Kurangi stok
            $updateStok = mysqli_query($koneksi, "
                UPDATE data_lab 
                SET stok = stok - 1 
                WHERE nama_lab = '$nama_lab'
            ");

            if (!$updateStok) {
                throw new Exception("Gagal mengurangi stok.");
            }

            // Commit jika semua berhasil
            mysqli_commit($koneksi);

            header("Location: dashboard_mhs.php?msg=success");
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
<title>Ajukan Peminjaman – LabSystem</title>

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
    max-width: 820px;
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

.brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.brand-icon {
    width: 32px; height: 32px;
    background: var(--accent);
    border-radius: 8px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.brand-icon i { color: #fff; font-size: 15px; }

.brand-text strong {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.brand-text span { font-size: 11px; color: var(--muted); }

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

/* ── PAGE HEADER ── */
.page-header {
    margin-bottom: 24px;
}

.page-header h1 {
    font-size: 20px;
    font-weight: 600;
    letter-spacing: -.3px;
    margin-bottom: 3px;
}

.page-header p { font-size: 13px; color: var(--muted); }

/* ── ERROR ALERT ── */
.alert-error {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: var(--red-soft);
    border: 1px solid #FECACA;
    border-radius: var(--radius);
    padding: 13px 16px;
    margin-bottom: 20px;
    font-size: 13.5px;
    color: var(--red);
}

.alert-error i { font-size: 16px; flex-shrink: 0; margin-top: 1px; }

/* ── LAYOUT ── */
.form-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 16px;
    align-items: start;
}

/* ── CARD ── */
.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    margin-bottom: 16px;
}

.card:last-child { margin-bottom: 0; }

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

.card-body { padding: 20px; }

/* ── READONLY PROFILE GRID ── */
.profile-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}

.profile-field {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.profile-field:nth-child(even) { border-right: none; }
.profile-field:nth-last-child(-n+2) { border-bottom: none; }

.field-label {
    font-size: 10.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--muted);
}

.field-value {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text);
}

.field-value.mono {
    font-family: 'DM Mono', monospace;
    font-size: 13px;
}

/* ── FORM ELEMENTS ── */
.form-group { margin-bottom: 16px; }
.form-group:last-child { margin-bottom: 0; }

.form-row {
    display: grid;
    gap: 14px;
    margin-bottom: 16px;
}

.form-row-2 { grid-template-columns: 1fr 1fr; }

label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--muted);
    margin-bottom: 6px;
}

input[type="date"],
input[type="time"],
select {
    width: 100%;
    padding: 9px 12px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13.5px;
    color: var(--text);
    outline: none;
    transition: border-color .15s, background .15s;
    appearance: none;
    -webkit-appearance: none;
}

input[type="date"]:focus,
input[type="time"]:focus,
select:focus {
    border-color: var(--accent);
    background: var(--surface);
    box-shadow: 0 0 0 3px rgba(26,26,26,.06);
}

select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238C8C8A' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 36px;
    cursor: pointer;
}

.section-divider {
    height: 1px;
    background: var(--border);
    margin: 18px 0;
}

/* ── SUMMARY CARD ── */
.summary-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    position: sticky;
    top: 24px;
}

.summary-header {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.summary-body { padding: 18px; }

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 12px;
}

.summary-row:last-child { margin-bottom: 0; }

.summary-key {
    font-size: 12px;
    font-weight: 500;
    color: var(--muted);
    flex-shrink: 0;
}

.summary-val {
    font-size: 12.5px;
    font-weight: 500;
    color: var(--text);
    text-align: right;
    font-family: 'DM Mono', monospace;
    word-break: break-all;
}

.summary-divider { height: 1px; background: var(--border); margin: 14px 0; }

.summary-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: var(--warn-soft);
    color: var(--warn);
    font-size: 11.5px;
    font-weight: 500;
    padding: 4px 9px;
    border-radius: 100px;
}

.summary-status::before {
    content: '';
    width: 5px; height: 5px;
    border-radius: 50%;
    background: var(--warn);
    flex-shrink: 0;
}

/* ── STUDENT PILL ── */
.student-pill {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    margin-bottom: 14px;
}

.student-avatar {
    width: 34px; height: 34px;
    background: var(--accent);
    border-radius: 50%;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    font-family: 'DM Mono', monospace;
    font-size: 13px;
    font-weight: 600;
    color: #fff;
}

.student-info strong {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.student-info span {
    font-family: 'DM Mono', monospace;
    font-size: 11.5px;
    color: var(--muted);
}

/* ── ACTION BUTTONS ── */
.form-actions { display: flex; flex-direction: column; gap: 10px; margin-top: 18px; }

.btn-submit {
    width: 100%;
    padding: 11px;
    background: var(--accent);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 13.5px;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    transition: opacity .15s;
}

.btn-submit:hover { opacity: .85; }

.btn-cancel {
    width: 100%;
    padding: 10px;
    background: transparent;
    color: var(--muted);
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    border: 1px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    text-decoration: none;
    transition: background .15s, color .15s;
}

.btn-cancel:hover { background: var(--bg); color: var(--text); }

/* ── RESPONSIVE ── */
@media (max-width: 860px) {
    .form-layout { grid-template-columns: 1fr; }
    .summary-card { position: static; }
}

@media (max-width: 560px) {
    .page-wrap { padding: 16px 16px 48px; }
    .profile-grid { grid-template-columns: 1fr; }
    .profile-field { border-right: none !important; }
    .profile-field:nth-last-child(-n+2) { border-bottom: 1px solid var(--border); }
    .profile-field:last-child { border-bottom: none; }
    .form-row-2 { grid-template-columns: 1fr; }
    .top-nav { flex-direction: column; align-items: flex-start; }
}
</style>
</head>
<body>

<div class="page-wrap">

    <!-- Top Nav -->
    <div class="top-nav">
        <div class="brand">
            <div class="brand-icon"><i class="bi bi-boxes"></i></div>
            <div class="brand-text">
                <strong>LabSystem</strong>
                <span>Portal Mahasiswa</span>
            </div>
        </div>
        <a href="dashboard_mhs.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <h1>Ajukan Peminjaman</h1>
        <p>Isi formulir berikut untuk mengajukan peminjaman laboratorium</p>
    </div>

    <?php if (isset($error)): ?>
    <div class="alert-error">
        <i class="bi bi-exclamation-circle-fill"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" id="loanForm">
    <div class="form-layout">

        <!-- LEFT: Form Fields -->
        <div>

            <!-- Card: Identitas Mahasiswa -->
            <div class="card">
                <div class="card-header">
                    <div class="card-header-icon"><i class="bi bi-person-badge"></i></div>
                    <h2>Identitas Mahasiswa</h2>
                </div>
                <div class="profile-grid">
                    <div class="profile-field">
                        <span class="field-label">Nama</span>
                        <span class="field-value"><?= htmlspecialchars($dataMhs['nama']) ?></span>
                    </div>
                    <div class="profile-field">
                        <span class="field-label">NIM</span>
                        <span class="field-value mono"><?= htmlspecialchars($nim) ?></span>
                    </div>
                    <div class="profile-field">
                        <span class="field-label">No. Telepon</span>
                        <span class="field-value mono"><?= htmlspecialchars($dataMhs['no_telepon']) ?></span>
                    </div>
                    <div class="profile-field">
                        <span class="field-label">Alamat</span>
                        <span class="field-value"><?= htmlspecialchars($dataMhs['alamat']) ?></span>
                    </div>
                </div>

                <!-- Hidden inputs to pass data (matching original form fields) -->
                <input type="hidden" name="nama" value="<?= htmlspecialchars($dataMhs['nama']) ?>">
                <input type="hidden" name="nim_val" value="<?= htmlspecialchars($nim) ?>">
                <input type="hidden" name="no_telepon" value="<?= htmlspecialchars($dataMhs['no_telepon']) ?>">
                <input type="hidden" name="alamat" value="<?= htmlspecialchars($dataMhs['alamat']) ?>">
            </div>

            <!-- Card: Detail Peminjaman -->
            <div class="card">
                <div class="card-header">
                    <div class="card-header-icon"><i class="bi bi-building"></i></div>
                    <h2>Detail Peminjaman</h2>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label>Laboratorium</label>
                        <select name="nama_lab" id="selectLab" required>
                            <option value="">— Pilih Laboratorium —</option>
                            <?php while ($lab = mysqli_fetch_assoc($labQuery)): ?>
                                <option value="<?= htmlspecialchars($lab['nama_lab']) ?>">
                                    <?= htmlspecialchars($lab['nama_lab']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="section-divider"></div>

                    <div class="form-row form-row-2">
                        <div class="form-group" style="margin-bottom:0">
                            <label>Jam Mulai</label>
                            <input type="time" name="jam_mulai" id="inputMulai" required>
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label>Jam Selesai</label>
                            <input type="time" name="jam_selesai" id="inputSelesai" required>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <div class="form-group" style="margin-bottom:0">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" id="inputTanggal" required>
                    </div>

                </div>
            </div>

        </div>

        <!-- RIGHT: Summary + Submit -->
        <div>
            <div class="summary-card">
                <div class="summary-header">Ringkasan Pengajuan</div>
                <div class="summary-body">

                    <!-- Student pill -->
                    <div class="student-pill">
                        <div class="student-avatar"><?= strtoupper(substr($dataMhs['nama'], 0, 1)) ?></div>
                        <div class="student-info">
                            <strong><?= htmlspecialchars($dataMhs['nama']) ?></strong>
                            <span><?= htmlspecialchars($nim) ?></span>
                        </div>
                    </div>

                    <div class="summary-row">
                        <span class="summary-key">Status</span>
                        <span class="summary-status">Menunggu</span>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row">
                        <span class="summary-key">Lab</span>
                        <span class="summary-val" id="sumLab">—</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Tanggal</span>
                        <span class="summary-val" id="sumTanggal">—</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Waktu</span>
                        <span class="summary-val" id="sumWaktu">—</span>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="simpan" class="btn-submit">
                            <i class="bi bi-send"></i> Kirim Pengajuan
                        </button>
                        <a href="dashboard_mhs.php" class="btn-cancel">
                            <i class="bi bi-x"></i> Batal
                        </a>
                    </div>

                </div>
            </div>
        </div>

    </div>
    </form>

</div>

<script>
// Live summary
const selectLab    = document.getElementById('selectLab');
const inputTanggal = document.getElementById('inputTanggal');
const inputMulai   = document.getElementById('inputMulai');
const inputSelesai = document.getElementById('inputSelesai');

function updateSummary() {
    const lab     = selectLab.value || '—';
    const tanggal = inputTanggal.value || '';
    const mulai   = inputMulai.value   || '';
    const selesai = inputSelesai.value || '';

    document.getElementById('sumLab').textContent = lab;
    document.getElementById('sumTanggal').textContent = tanggal
        ? new Date(tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        : '—';
    document.getElementById('sumWaktu').textContent = mulai && selesai
        ? mulai + ' – ' + selesai
        : mulai || '—';
}

selectLab.addEventListener('change', updateSummary);
inputTanggal.addEventListener('change', updateSummary);
inputMulai.addEventListener('change', updateSummary);
inputSelesai.addEventListener('change', updateSummary);
</script>

</body>
</html>