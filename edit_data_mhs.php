<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$nim = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
if ($nim === '') {
    header('Location: data_mhs.php');
    exit;
}

$mhsQuery = mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE nim = '$nim' LIMIT 1");
$mhs = mysqli_fetch_assoc($mhsQuery);

if (!$mhs) {
    header('Location: data_mhs.php?error=not_found');
    exit;
}

/* Simpan perubahan */
if (isset($_POST['simpan'])) {

    $nama       = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $no_telepon = mysqli_real_escape_string($koneksi, trim($_POST['no_telepon']));
    $alamat     = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));
    $password   = trim($_POST['password'] ?? '');

    if ($nama === '' || $no_telepon === '' || $alamat === '') {
        $error = "Lengkapi semua data mahasiswa.";
    } else {

        if ($password !== '') {
            $passwordEsc = mysqli_real_escape_string($koneksi, $password);
            $update = mysqli_query($koneksi, "
                UPDATE mahasiswa
                SET nama = '$nama', no_telepon = '$no_telepon', alamat = '$alamat', password = '$passwordEsc'
                WHERE nim = '$nim'
            ");
        } else {
            $update = mysqli_query($koneksi, "
                UPDATE mahasiswa
                SET nama = '$nama', no_telepon = '$no_telepon', alamat = '$alamat'
                WHERE nim = '$nim'
            ");
        }

        if (!$update) {
            $error = "Gagal memperbarui data mahasiswa.";
        } else {
            header("Location: data_mhs.php?msg=updated");
            exit;
        }
    }

    // Supaya form tetap menampilkan input terbaru jika gagal
    $mhs['nama']       = $_POST['nama'];
    $mhs['no_telepon'] = $_POST['no_telepon'];
    $mhs['alamat']     = $_POST['alamat'];
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ubah Mahasiswa – LabSystem</title>

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
    --warn:      #D97706;
    --warn-soft: #FFFBEB;
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

.form-row-nim { grid-template-columns: 160px 1fr; }

label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 6px;
}

.hint {
    font-size: 11.5px;
    color: var(--muted);
    margin-top: 6px;
}

input[type="text"],
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
}

input[type="text"]:focus,
textarea:focus {
    border-color: var(--accent);
    background: var(--surface);
    box-shadow: 0 0 0 3px rgba(26,26,26,.06);
}

input.nim-input { font-family: 'DM Mono', monospace; font-size: 13px; }

input[readonly] {
    background: var(--bg);
    color: var(--muted);
    cursor: not-allowed;
}

textarea { resize: vertical; min-height: 72px; }

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

.summary-avatar-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.summary-avatar {
    width: 36px; height: 36px;
    background: var(--accent);
    border-radius: 50%;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    font-family: 'DM Mono', monospace;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
}

.summary-avatar-info strong {
    display: block;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text);
}

.summary-avatar-info span {
    font-size: 11.5px;
    color: var(--muted);
    font-family: 'DM Mono', monospace;
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
    <span class="topbar-title">Ubah Mahasiswa</span>
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
    </ul>

    <p class="nav-section">Aset</p>
    <ul style="list-style:none;padding:0;margin:0">
        <li class="nav-item">
            <a class="nav-link" href="data_lab.php">
                <i class="bi bi-building-fill"></i> Laboratorium
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="data_barang.php">
                <i class="bi bi-box-seam-fill"></i> Data Barang
            </a>
        </li>
    </ul>

    <p class="nav-section">Peminjaman</p>
    <ul style="list-style:none;padding:0;margin:0">
        <li class="nav-item">
            <a class="nav-link" href="riwayat_pinjam.php">
                <i class="bi bi-clock-history"></i> Ongoing
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="arsip_peminjaman.php">
                <i class="bi bi-archive-fill"></i> Arsip
            </a>
        </li>
    </ul>

    <p class="nav-section">Pengguna</p>
    <ul style="list-style:none;padding:0;margin:0">
        <li class="nav-item">
            <a class="nav-link active" href="data_mhs.php">
                <i class="bi bi-people-fill"></i> Mahasiswa
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="data_admin.php">
                <i class="bi bi-person-badge-fill"></i> Admin
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
        <a href="data_mhs.php" class="page-header-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="page-header-text">
            <h1>Ubah Mahasiswa</h1>
            <p>Perbarui data <?= htmlspecialchars($mhs['nama']) ?></p>
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
            <div>

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
                                <input type="text" class="nim-input" value="<?= htmlspecialchars($mhs['nim']) ?>" readonly>
                                <p class="hint">NIM tidak dapat diubah.</p>
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama" id="inputNama" value="<?= htmlspecialchars($mhs['nama']) ?>" required>
                            </div>
                        </div>

                        <div class="section-divider"></div>

                        <div class="form-group">
                            <label>No. Telepon</label>
                            <input type="text" name="no_telepon" id="inputTelepon" value="<?= htmlspecialchars($mhs['no_telepon']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea name="alamat" id="inputAlamat" required><?= htmlspecialchars($mhs['alamat']) ?></textarea>
                        </div>

                        <div class="section-divider"></div>

                        <div class="form-group" style="margin-bottom:0">
                            <label>Ganti Password (opsional)</label>
                            <input type="text" name="password" id="inputPassword" placeholder="Kosongkan jika tidak ingin mengganti password">
                            <p class="hint">Isi hanya jika ingin mereset password mahasiswa ini.</p>
                        </div>

                    </div>
                </div>

            </div>

            <!-- RIGHT: Summary + Actions -->
            <div>
                <div class="summary-card">
                    <div class="summary-header">Ringkasan Mahasiswa</div>
                    <div class="summary-body">

                        <div class="summary-avatar-row">
                            <div class="summary-avatar" id="sumAvatar"><?= strtoupper(substr($mhs['nama'], 0, 1)) ?></div>
                            <div class="summary-avatar-info">
                                <strong id="sumNama"><?= htmlspecialchars($mhs['nama']) ?></strong>
                                <span><?= htmlspecialchars($mhs['nim']) ?></span>
                            </div>
                        </div>

                        <div class="summary-divider"></div>

                        <div class="summary-row">
                            <span class="summary-key">No. Telepon</span>
                            <span class="summary-val" id="sumTelepon"><?= htmlspecialchars($mhs['no_telepon']) ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-key">Alamat</span>
                            <span class="summary-val" id="sumAlamat"><?= htmlspecialchars($mhs['alamat']) ?></span>
                        </div>

                        <div class="summary-divider"></div>

                        <div class="form-actions">
                            <button type="submit" name="simpan" class="btn-submit">
                                <i class="bi bi-check-lg"></i> Simpan Perubahan
                            </button>
                            <a href="data_mhs.php" class="btn-cancel">
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
const inputNama    = document.getElementById('inputNama');
const inputTelepon = document.getElementById('inputTelepon');
const inputAlamat  = document.getElementById('inputAlamat');

function updateSummary() {
    const nama = inputNama.value.trim();
    document.getElementById('sumNama').textContent    = nama || '—';
    document.getElementById('sumTelepon').textContent = inputTelepon.value || '—';
    document.getElementById('sumAlamat').textContent  = inputAlamat.value || '—';
    document.getElementById('sumAvatar').textContent  = nama ? nama.charAt(0).toUpperCase() : '?';
}

inputNama.addEventListener('input', updateSummary);
inputTelepon.addEventListener('input', updateSummary);
inputAlamat.addEventListener('input', updateSummary);
</script>

</body>
</html>