<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

/* Ambil lab yang tersedia */
$labQuery = mysqli_query($koneksi, "
    SELECT id_lab, nama_lab
    FROM data_lab
    WHERE status = 'availabel'
    ORDER BY nama_lab ASC
");

/* Simpan data */
/* Simpan data */
if (isset($_POST['simpan'])) {

    $nim            = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $nama_mahasiswa = mysqli_real_escape_string($koneksi, $_POST['nama_mahasiswa']);
    $no_telp        = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $alamat         = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $nama_lab       = mysqli_real_escape_string($koneksi, $_POST['nama_lab']);
    $tanggal        = $_POST['tanggal'];
    $jam_mulai      = $_POST['jam_mulai'];
    $jam_selesai    = $_POST['jam_selesai'];
    $status         = 'menunggu';

    if ($jam_selesai <= $jam_mulai) {
        $error = "Jam selesai harus lebih besar dari jam mulai.";
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
                (nim, nama_mahasiswa, no_telp, alamat, nama_lab, tanggal, jam_mulai, jam_selesai, status)
                VALUES
                ('$nim', '$nama_mahasiswa', '$no_telp', '$alamat', '$nama_lab', '$tanggal', '$jam_mulai', '$jam_selesai', '$status')
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

            header("Location: dashboard.php?msg=success");
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
<title>Tambah Peminjaman – LabSystem</title>

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
    --blue:      #2563EB;
    --blue-soft: #EFF4FF;
    --red:       #DC2626;
    --red-soft:  #FEF2F2;
    --green:     #16A34A;
    --green-soft:#F0FDF4;
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

.sidebar-logo-text span {
    font-size: 11px;
    color: var(--muted);
}

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
.nav-link.danger { color: #DC2626; }
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
    padding: 36px 40px;
}

.page-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 28px;
}

.page-header-back {
    width: 34px; height: 34px;
    border: 1px solid var(--border);
    background: var(--surface);
    border-radius: 8px;
    display: grid;
    place-items: center;
    text-decoration: none;
    color: var(--text);
    font-size: 15px;
    flex-shrink: 0;
    transition: background .15s;
}

.page-header-back:hover { background: var(--bg); color: var(--text); }

.page-header-text h1 {
    font-size: 18px;
    font-weight: 600;
    letter-spacing: -.3px;
    margin-bottom: 1px;
}

.page-header-text p { font-size: 12.5px; color: var(--muted); }

/* ── FORM LAYOUT ── */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-items: start;
}

.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 10px;
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

/* ── FORM ELEMENTS ── */
.form-group { margin-bottom: 16px; }
.form-group:last-child { margin-bottom: 0; }

.form-row {
    display: grid;
    gap: 14px;
    margin-bottom: 16px;
}

.form-row-2 { grid-template-columns: 1fr 1fr; }
.form-row-3 { grid-template-columns: 1fr 1fr 1fr; }
.form-row-nim { grid-template-columns: 140px 1fr; }

label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 6px;
}

input[type="text"],
input[type="date"],
input[type="time"],
select,
textarea {
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

input[type="text"]:focus,
input[type="date"]:focus,
input[type="time"]:focus,
select:focus,
textarea:focus {
    border-color: var(--accent);
    background: var(--surface);
    box-shadow: 0 0 0 3px rgba(26,26,26,.06);
}

input.nim-input { font-family: 'DM Mono', monospace; font-size: 13px; }

textarea { resize: vertical; min-height: 72px; }

select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238C8C8A' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 36px;
    cursor: pointer;
}

/* ── ALERT ── */
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

/* ── SIDEBAR SUMMARY CARD ── */
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
    margin-bottom: 14px;
    font-size: 13px;
}

.summary-row:last-child { margin-bottom: 0; }

.summary-key {
    color: var(--muted);
    font-size: 12px;
    font-weight: 500;
    flex-shrink: 0;
}

.summary-val {
    font-weight: 500;
    color: var(--text);
    text-align: right;
    font-family: 'DM Mono', monospace;
    font-size: 12.5px;
    word-break: break-all;
}

.summary-divider {
    height: 1px;
    background: var(--border);
    margin: 14px 0;
}

.summary-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #FFFBEB;
    color: #D97706;
    font-size: 11.5px;
    font-weight: 500;
    padding: 4px 9px;
    border-radius: 100px;
}

.summary-status::before {
    content: '';
    width: 5px; height: 5px;
    border-radius: 50%;
    background: #D97706;
    flex-shrink: 0;
}

/* ── BUTTONS ── */
.form-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 20px;
}

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
    text-decoration: none;
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

/* ── DIVIDER ── */
.section-divider {
    height: 1px;
    background: var(--border);
    margin: 20px 0;
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
@media (max-width: 960px) {
    .form-grid { grid-template-columns: 1fr; }
    .summary-card { position: static; }
}

@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.show { transform: translateX(0); }
    .sidebar-overlay.show { display: block; }
    .topbar { display: flex; }
    .main { margin-left: 0; padding: 16px; }
    .form-row-nim { grid-template-columns: 1fr; }
    .form-row-3 { grid-template-columns: 1fr 1fr; }
}

@media (max-width: 480px) {
    .form-row-2,
    .form-row-3 { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="overlay"></div>

<!-- Mobile Topbar -->
<header class="topbar">
    <button class="btn-icon" id="toggleSidebar">
        <i class="bi bi-list"></i>
    </button>
    <span class="topbar-title">Tambah Peminjaman</span>
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
            <a class="nav-link active" href="tambah_data_pinjam.php">
                <i class="bi bi-plus-circle-fill"></i> Tambah Peminjaman
            </a>
        </li>
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
        <a href="dashboard.php" class="page-header-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="page-header-text">
            <h1>Tambah Peminjaman</h1>
            <p>Isi formulir untuk mendaftarkan peminjaman laboratorium baru</p>
        </div>
    </div>

    <?php if (isset($error)): ?>
    <div class="alert-error">
        <i class="bi bi-exclamation-circle-fill"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" id="mainForm">

        <div class="form-grid">

            <!-- LEFT: Form Fields -->
            <div style="display:flex;flex-direction:column;gap:16px;">

                <!-- Card: Data Mahasiswa -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-header-icon">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <h2>Data Mahasiswa</h2>
                    </div>
                    <div class="card-body">

                        <div class="form-row form-row-nim">
                            <div class="form-group" style="margin-bottom:0">
                                <label>NIM</label>
                                <input type="text" name="nim" class="nim-input" placeholder="e.g. 2201234567" required>
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label>Nama Mahasiswa</label>
                                <input type="text" name="nama_mahasiswa" placeholder="Nama lengkap" required>
                            </div>
                        </div>

                        <div class="section-divider"></div>

                        <div class="form-row form-row-2">
                            <div class="form-group" style="margin-bottom:0">
                                <label>No. Telepon</label>
                                <input type="text" name="no_telp" placeholder="08xxxxxxxxxx" required>
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label>Alamat</label>
                                <textarea name="alamat" placeholder="Alamat lengkap mahasiswa" required></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Card: Detail Peminjaman -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-header-icon">
                            <i class="bi bi-building"></i>
                        </div>
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

                        <div class="form-row form-row-3">
                            <div class="form-group" style="margin-bottom:0">
                                <label>Tanggal</label>
                                <input type="date" name="tanggal" id="inputTanggal" required>
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label>Jam Mulai</label>
                                <input type="time" name="jam_mulai" id="inputMulai" required>
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label>Jam Selesai</label>
                                <input type="time" name="jam_selesai" id="inputSelesai" required>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- RIGHT: Summary + Actions -->
            <div>
                <div class="summary-card">
                    <div class="summary-header">Ringkasan Peminjaman</div>
                    <div class="summary-body">

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

                        <div class="summary-divider"></div>

                        <div class="form-actions">
                            <button type="submit" name="simpan" class="btn-submit">
                                <i class="bi bi-check-lg"></i> Simpan Peminjaman
                            </button>
                            <a href="dashboard.php" class="btn-cancel">
                                <i class="bi bi-x"></i> Batal
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </form>

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

// Live summary update
const selectLab    = document.getElementById('selectLab');
const inputTanggal = document.getElementById('inputTanggal');
const inputMulai   = document.getElementById('inputMulai');
const inputSelesai = document.getElementById('inputSelesai');

function updateSummary() {
    const lab     = selectLab.value    || '—';
    const tanggal = inputTanggal.value || '';
    const mulai   = inputMulai.value   || '';
    const selesai = inputSelesai.value || '';

    document.getElementById('sumLab').textContent     = lab;
    document.getElementById('sumTanggal').textContent = tanggal
        ? new Date(tanggal).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
        : '—';
    document.getElementById('sumWaktu').textContent   = (mulai && selesai)
        ? mulai + ' – ' + selesai
        : (mulai || '—');
}

selectLab.addEventListener('change', updateSummary);
inputTanggal.addEventListener('change', updateSummary);
inputMulai.addEventListener('change', updateSummary);
inputSelesai.addEventListener('change', updateSummary);
</script>

</body>
</html>