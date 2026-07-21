<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

/* ===============================
   CEK LOGIN MAHASISWA
================================ */
if (!isset($_SESSION['mahasiswa']['nim'])) {
    header('Location: ../auth/login_mhs.php');
    exit;
}

$nim = $_SESSION['mahasiswa']['nim'];

/* ===============================
   AMBIL DATA MAHASISWA
================================ */
$stmtMhs = mysqli_prepare($koneksi, "SELECT nama, no_telepon, alamat FROM mahasiswa WHERE nim = ?");
mysqli_stmt_bind_param($stmtMhs, "s", $nim);
mysqli_stmt_execute($stmtMhs);
$dataMhs = mysqli_stmt_get_result($stmtMhs)->fetch_assoc();

if (!$dataMhs) {
    die("Data mahasiswa tidak ditemukan.");
}

/* ===============================
   AMBIL LAB TERSEDIA + JUMLAH KURSI
================================ */
$labQuery = mysqli_query($koneksi, "
    SELECT nama_lab, stok
    FROM data_lab
    WHERE status = 'availabel' AND stok > 0
    ORDER BY nama_lab ASC
");

$labsData = [];
while ($lab = mysqli_fetch_assoc($labQuery)) {
    $labsData[] = [
        'nama_lab' => $lab['nama_lab'],
        'stok'     => (int) $lab['stok'],
    ];
}

$labsMapJs = [];
foreach ($labsData as $l) {
    $labsMapJs[$l['nama_lab']] = $l['stok'];
}

/* ===============================
   AMBIL BARANG TERSEDIA
================================ */
$barangQuery = mysqli_query($koneksi, "
    SELECT b.id_barang, b.nama_barang, b.kode_barang, b.stok, l.nama_lab
    FROM data_barang b
    JOIN data_lab l ON b.id_lab = l.id_lab
    WHERE b.status = 'availabel' AND b.stok > 0
    ORDER BY b.nama_barang ASC
");

$barangData = [];
while ($b = mysqli_fetch_assoc($barangQuery)) {
    $barangData[] = $b;
}

$barangMapJs = [];
foreach ($barangData as $b) {
    $barangMapJs[$b['id_barang']] = [
        'stok'     => (int) $b['stok'],
        'nama'     => $b['nama_barang'],
        'nama_lab' => $b['nama_lab'],
    ];
}

/* ===============================
   PROSES SIMPAN PINJAM
================================ */
if (isset($_POST['simpan'])) {

    $jenis       = $_POST['jenis'] ?? 'lab';
    $tanggal     = $_POST['tanggal'] ?? '';
    $jam_mulai   = $_POST['jam_mulai'] ?? '';
    $jam_selesai = $_POST['jam_selesai'] ?? '';
    $status      = 'menunggu';

    if ($tanggal === '' || $jam_mulai === '' || $jam_selesai === '') {
        $error = "Tanggal dan jam wajib diisi.";
    } elseif ($jam_selesai <= $jam_mulai) {
        $error = "Jam selesai harus lebih besar dari jam mulai";
    } elseif ($jenis === 'lab') {

        $nama_lab = trim($_POST['nama_lab'] ?? '');
        $kursi    = (int) ($_POST['kursi'] ?? 0);

        if ($nama_lab === '') {
            $error = "Silakan pilih laboratorium terlebih dahulu.";
        } elseif ($kursi <= 0) {
            $error = "Silakan pilih kursi terlebih dahulu.";
        } else {

            mysqli_begin_transaction($koneksi);

            try {
                // Kunci baris lab yang dipilih & cek stok tersisa
                $stmtLabLock = mysqli_prepare($koneksi, "SELECT stok FROM data_lab WHERE nama_lab = ? FOR UPDATE");
                mysqli_stmt_bind_param($stmtLabLock, "s", $nama_lab);
                mysqli_stmt_execute($stmtLabLock);
                $rowLab = mysqli_stmt_get_result($stmtLabLock)->fetch_assoc();

                if (!$rowLab || (int)$rowLab['stok'] <= 0) {
                    throw new Exception("Stok laboratorium ini sudah habis.");
                }

                // Cek apakah kursi ini sudah dipesan pada rentang waktu yang sama
                $stmtCekKursi = mysqli_prepare($koneksi, "
                    SELECT id_data
                    FROM data_pinjam
                    WHERE jenis        = 'lab'
                      AND nama_lab     = ?
                      AND tanggal      = ?
                      AND kursi        = ?
                      AND status IN ('menunggu', 'disetujui')
                      AND jam_mulai    < ?
                      AND jam_selesai  > ?
                    FOR UPDATE
                ");
                mysqli_stmt_bind_param($stmtCekKursi, "ssiss", $nama_lab, $tanggal, $kursi, $jam_selesai, $jam_mulai);
                mysqli_stmt_execute($stmtCekKursi);
                $cekKursi = mysqli_stmt_get_result($stmtCekKursi);

                if ($cekKursi && mysqli_num_rows($cekKursi) > 0) {
                    throw new Exception("Kursi tersebut sudah dipesan pada rentang waktu yang dipilih.");
                }

                // Insert data peminjaman
                $stmtInsert = mysqli_prepare($koneksi, "
                    INSERT INTO data_pinjam (nim, jenis, nama_lab, kursi, tanggal, jam_mulai, jam_selesai, status)
                    VALUES (?, 'lab', ?, ?, ?, ?, ?, ?)
                ");
                mysqli_stmt_bind_param($stmtInsert, "ssissss", $nim, $nama_lab, $kursi, $tanggal, $jam_mulai, $jam_selesai, $status);
                if (!mysqli_stmt_execute($stmtInsert)) {
                    throw new Exception("Gagal menyimpan data peminjaman.");
                }

                // Kurangi stok lab sebanyak 1
                $stmtDec = mysqli_prepare($koneksi, "UPDATE data_lab SET stok = stok - 1 WHERE nama_lab = ? AND stok > 0");
                mysqli_stmt_bind_param($stmtDec, "s", $nama_lab);
                if (!mysqli_stmt_execute($stmtDec) || mysqli_stmt_affected_rows($stmtDec) === 0) {
                    throw new Exception("Gagal memperbarui stok laboratorium.");
                }

                mysqli_commit($koneksi);
                header("Location: dashboard_mhs.php?msg=success");
                exit;

            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                $error = $e->getMessage();
            }
        }

    } elseif ($jenis === 'barang') {

        $id_barang = (int) ($_POST['id_barang'] ?? 0);
        $jumlah    = (int) ($_POST['jumlah'] ?? 0);

        if ($id_barang <= 0) {
            $error = "Silakan pilih barang terlebih dahulu.";
        } elseif ($jumlah <= 0) {
            $error = "Jumlah barang harus lebih dari 0.";
        } else {

            mysqli_begin_transaction($koneksi);

            try {
                // Kunci baris barang yang dipilih & cek stok tersisa
                $stmtBrgLock = mysqli_prepare($koneksi, "
                    SELECT b.stok, b.nama_barang, l.nama_lab
                    FROM data_barang b
                    JOIN data_lab l ON b.id_lab = l.id_lab
                    WHERE b.id_barang = ?
                    FOR UPDATE
                ");
                mysqli_stmt_bind_param($stmtBrgLock, "i", $id_barang);
                mysqli_stmt_execute($stmtBrgLock);
                $rowBrg = mysqli_stmt_get_result($stmtBrgLock)->fetch_assoc();

                if (!$rowBrg) {
                    throw new Exception("Barang tidak ditemukan.");
                }
                if ((int)$rowBrg['stok'] < $jumlah) {
                    throw new Exception("Stok barang tidak mencukupi. Sisa stok: " . (int)$rowBrg['stok']);
                }

                // Insert data peminjaman
                $stmtInsert = mysqli_prepare($koneksi, "
                    INSERT INTO data_pinjam (nim, jenis, nama_lab, id_barang, nama_barang, jumlah, tanggal, jam_mulai, jam_selesai, status)
                    VALUES (?, 'barang', ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                mysqli_stmt_bind_param($stmtInsert, "ssisissss",
                    $nim, $rowBrg['nama_lab'], $id_barang, $rowBrg['nama_barang'], $jumlah, $tanggal, $jam_mulai, $jam_selesai, $status);
                if (!mysqli_stmt_execute($stmtInsert)) {
                    throw new Exception("Gagal menyimpan data peminjaman.");
                }

                // Kurangi stok barang sebanyak jumlah yang dipinjam
                $stmtDec = mysqli_prepare($koneksi, "UPDATE data_barang SET stok = stok - ? WHERE id_barang = ? AND stok >= ?");
                mysqli_stmt_bind_param($stmtDec, "iii", $jumlah, $id_barang, $jumlah);
                if (!mysqli_stmt_execute($stmtDec) || mysqli_stmt_affected_rows($stmtDec) === 0) {
                    throw new Exception("Gagal memperbarui stok barang.");
                }

                mysqli_commit($koneksi);
                header("Location: dashboard_mhs.php?msg=success");
                exit;

            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                $error = $e->getMessage();
            }
        }

    } else {
        $error = "Jenis peminjaman tidak valid.";
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
.page-header { margin-bottom: 24px; }
.page-header h1 { font-size: 20px; font-weight: 600; letter-spacing: -.3px; margin-bottom: 3px; }
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

.card-header h2 { font-size: 13.5px; font-weight: 600; color: var(--text); }
.card-header-hint { margin-left: auto; font-size: 11px; color: var(--muted); }
.card-body { padding: 20px; }

/* ── READONLY PROFILE GRID ── */
.profile-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }

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

.field-value { font-size: 13.5px; font-weight: 500; color: var(--text); }
.field-value.mono { font-family: 'DM Mono', monospace; font-size: 13px; }

/* ── SEGMENTED TOGGLE (Jenis Peminjaman) ── */
.segmented {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 18px;
}

.segmented-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    background: var(--bg);
    border: 1.5px solid var(--border);
    border-radius: 9px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--muted);
    cursor: pointer;
    transition: background .15s, border-color .15s, color .15s;
    user-select: none;
}

.segmented-btn i { font-size: 15px; }

.segmented-btn.active {
    background: var(--accent);
    border-color: var(--accent);
    color: #fff;
}

/* ── FORM ELEMENTS ── */
.form-group { margin-bottom: 16px; }
.form-group:last-child { margin-bottom: 0; }

.form-row { display: grid; gap: 14px; margin-bottom: 16px; }
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
input[type="number"],
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
input[type="number"]:focus,
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

.section-divider { height: 1px; background: var(--border); margin: 18px 0; }
.stok-hint { font-size: 11.5px; color: var(--muted); margin-top: 6px; }
.stok-hint.warn { color: var(--warn); }

/* ── SEAT MAP ── */
.seat-empty-hint {
    padding: 30px 20px;
    text-align: center;
    font-size: 13px;
    color: var(--muted);
}

.seat-empty-hint i { display: block; font-size: 20px; margin-bottom: 8px; color: var(--border); }

.seat-map-wrap { padding: 22px 20px 20px; }

.admin-desk { display: flex; justify-content: center; margin-bottom: 26px; }

.admin-desk-label {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 26px;
    background: var(--accent);
    color: #fff;
    border-radius: 8px 8px 3px 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    position: relative;
}

.admin-desk-label::after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: -14px;
    transform: translateX(-50%);
    width: 70%;
    height: 3px;
    background: var(--border);
    border-radius: 2px;
}

.seat-rows {
    display: flex;
    flex-direction: row-reverse;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    margin-bottom: 22px;
    min-height: 44px;
}

.seat {
    width: 40px;
    height: 40px;
    border-radius: 8px 8px 4px 4px;
    border: 1px solid var(--border);
    background: var(--bg);
    display: grid;
    place-items: center;
    font-family: 'DM Mono', monospace;
    font-size: 12px;
    font-weight: 600;
    color: var(--muted);
    cursor: pointer;
    transition: background .15s, border-color .15s, color .15s, transform .1s;
    user-select: none;
    flex-shrink: 0;
}

.seat:hover:not(.taken) { border-color: var(--accent); color: var(--text); transform: translateY(-1px); }
.seat.selected { background: var(--accent); border-color: var(--accent); color: #fff; }
.seat.taken { background: var(--red-soft); border-color: #FECACA; color: var(--red); cursor: not-allowed; opacity: .75; }

.seat-loading { text-align: center; font-size: 12.5px; color: var(--muted); margin-bottom: 14px; }

.seat-legend {
    display: flex;
    gap: 18px;
    justify-content: center;
    flex-wrap: wrap;
    padding-top: 16px;
    border-top: 1px solid var(--border);
}

.legend-item { display: flex; align-items: center; gap: 7px; font-size: 11.5px; color: var(--muted); }

.legend-dot { width: 15px; height: 15px; border-radius: 4px; border: 1px solid var(--border); background: var(--bg); flex-shrink: 0; }
.legend-dot.selected { background: var(--accent); border-color: var(--accent); }
.legend-dot.taken { background: var(--red-soft); border-color: #FECACA; }

/* ── BARANG PICKER ── */
.barang-empty-hint {
    padding: 30px 20px;
    text-align: center;
    font-size: 13px;
    color: var(--muted);
}
.barang-empty-hint i { display: block; font-size: 20px; margin-bottom: 8px; color: var(--border); }

.barang-picker { padding: 20px; }

.barang-info-card {
    display: none;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: var(--violet-soft);
    border: 1px solid #DDD6FE;
    border-radius: 8px;
    margin-top: 14px;
}

.barang-info-icon {
    width: 34px; height: 34px;
    background: var(--violet);
    border-radius: 8px;
    display: grid;
    place-items: center;
    color: #fff;
    font-size: 15px;
    flex-shrink: 0;
}

.barang-info-text strong { display: block; font-size: 13px; color: var(--text); }
.barang-info-text span { font-size: 11.5px; color: var(--muted); }

/* ── SUMMARY CARD ── */
.summary-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    position: sticky;
    top: 24px;
}

.summary-header { padding: 14px 18px; border-bottom: 1px solid var(--border); font-size: 13px; font-weight: 600; color: var(--text); }
.summary-body { padding: 18px; }

.summary-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 12px; }
.summary-row:last-child { margin-bottom: 0; }

.summary-key { font-size: 12px; font-weight: 500; color: var(--muted); flex-shrink: 0; }
.summary-val { font-size: 12.5px; font-weight: 500; color: var(--text); text-align: right; font-family: 'DM Mono', monospace; word-break: break-all; }
.summary-val.seat-picked { color: var(--accent); font-weight: 600; }

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

.summary-status::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: var(--warn); flex-shrink: 0; }

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

.student-info strong { display: block; font-size: 13px; font-weight: 600; color: var(--text); }
.student-info span { font-family: 'DM Mono', monospace; font-size: 11.5px; color: var(--muted); }

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
    .seat { width: 34px; height: 34px; font-size: 11px; }
    .segmented { grid-template-columns: 1fr; }
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
        <p>Isi formulir berikut untuk mengajukan peminjaman laboratorium atau barang</p>
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
            </div>

            <!-- Card: Detail Peminjaman -->
            <div class="card">
                <div class="card-header">
                    <div class="card-header-icon"><i class="bi bi-clipboard-check"></i></div>
                    <h2>Detail Peminjaman</h2>
                </div>
                <div class="card-body">

                    <label>Jenis Peminjaman</label>
                    <div class="segmented">
                        <div class="segmented-btn active" data-jenis="lab" id="btnJenisLab">
                            <i class="bi bi-building"></i> Ruang Lab
                        </div>
                        <div class="segmented-btn" data-jenis="barang" id="btnJenisBarang">
                            <i class="bi bi-box-seam"></i> Barang / Alat
                        </div>
                    </div>
                    <input type="hidden" name="jenis" id="inputJenis" value="lab">

                    <div class="section-divider"></div>

                    <!-- Lab select (hanya untuk jenis = lab) -->
                    <div class="form-group" id="labSelectGroup">
                        <label>Laboratorium</label>
                        <select name="nama_lab" id="selectLab">
                            <option value="">— Pilih Laboratorium —</option>
                            <?php foreach ($labsData as $lab): ?>
                                <option value="<?= htmlspecialchars($lab['nama_lab']) ?>">
                                    <?= htmlspecialchars($lab['nama_lab']) ?> (<?= $lab['stok'] ?> kursi tersisa)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Barang select (hanya untuk jenis = barang) -->
                    <div class="form-group" id="barangSelectGroup" style="display:none">
                        <label>Barang / Alat</label>
                        <select name="id_barang" id="selectBarang">
                            <option value="">— Pilih Barang —</option>
                            <?php foreach ($barangData as $b): ?>
                                <option value="<?= (int)$b['id_barang'] ?>">
                                    <?= htmlspecialchars($b['nama_barang']) ?> — <?= htmlspecialchars($b['nama_lab']) ?> (stok: <?= (int)$b['stok'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($barangData)): ?>
                            <p class="stok-hint warn">Belum ada barang yang tersedia untuk dipinjam saat ini.</p>
                        <?php endif; ?>

                        <div class="form-group" style="margin-top:14px;margin-bottom:0">
                            <label>Jumlah</label>
                            <input type="number" name="jumlah" id="inputJumlah" min="1" value="1">
                            <p class="stok-hint" id="jumlahHint"></p>
                        </div>
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

            <!-- Card: Pilih Kursi (hanya jenis = lab) -->
            <div class="card" id="cardKursi">
                <div class="card-header">
                    <div class="card-header-icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>
                    <h2>Pilih Kursi</h2>
                    <span class="card-header-hint" id="seatCountHint"></span>
                </div>

                <div id="seatEmptyHint" class="seat-empty-hint">
                    <i class="bi bi-cursor"></i>
                    Pilih laboratorium terlebih dahulu untuk menampilkan denah kursi
                </div>

                <div id="seatCardBody" class="seat-map-wrap" style="display:none">
                    <div class="admin-desk">
                        <span class="admin-desk-label"><i class="bi bi-display"></i> Meja Admin</span>
                    </div>
                    <div id="seatLoading" class="seat-loading" style="display:none">Memeriksa ketersediaan kursi…</div>
                    <div id="seatRows" class="seat-rows"></div>
                    <div class="seat-legend">
                        <div class="legend-item"><span class="legend-dot"></span> Tersedia</div>
                        <div class="legend-item"><span class="legend-dot selected"></span> Dipilih</div>
                        <div class="legend-item"><span class="legend-dot taken"></span> Sudah dipesan</div>
                    </div>
                </div>

                <input type="hidden" name="kursi" id="inputKursiPilih" value="">
            </div>

            <!-- Card: Info Barang (hanya jenis = barang) -->
            <div class="card" id="cardBarangInfo" style="display:none">
                <div class="card-header">
                    <div class="card-header-icon"><i class="bi bi-info-circle"></i></div>
                    <h2>Info Barang</h2>
                </div>
                <div id="barangEmptyHint" class="barang-empty-hint">
                    <i class="bi bi-cursor"></i>
                    Pilih barang terlebih dahulu untuk melihat detailnya
                </div>
                <div class="barang-picker" id="barangPickerBody" style="display:none">
                    <div class="barang-info-card" id="barangInfoCard">
                        <div class="barang-info-icon"><i class="bi bi-box-seam-fill"></i></div>
                        <div class="barang-info-text">
                            <strong id="barangInfoNama">—</strong>
                            <span id="barangInfoLab">—</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT: Summary + Submit -->
        <div>
            <div class="summary-card">
                <div class="summary-header">Ringkasan Pengajuan</div>
                <div class="summary-body">

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
                        <span class="summary-key">Jenis</span>
                        <span class="summary-val" id="sumJenis">Ruang Lab</span>
                    </div>
                    <div class="summary-row" id="rowSumLab">
                        <span class="summary-key">Lab</span>
                        <span class="summary-val" id="sumLab">—</span>
                    </div>
                    <div class="summary-row" id="rowSumBarang" style="display:none">
                        <span class="summary-key">Barang</span>
                        <span class="summary-val" id="sumBarang">—</span>
                    </div>
                    <div class="summary-row" id="rowSumJumlah" style="display:none">
                        <span class="summary-key">Jumlah</span>
                        <span class="summary-val" id="sumJumlah">—</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Tanggal</span>
                        <span class="summary-val" id="sumTanggal">—</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Waktu</span>
                        <span class="summary-val" id="sumWaktu">—</span>
                    </div>
                    <div class="summary-row" id="rowSumKursi">
                        <span class="summary-key">Kursi</span>
                        <span class="summary-val" id="sumKursi">—</span>
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
/* Data dari PHP */
const labsData   = <?= json_encode($labsMapJs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const barangData = <?= json_encode($barangMapJs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

/* Elemen umum */
const inputJenis     = document.getElementById('inputJenis');
const btnJenisLab     = document.getElementById('btnJenisLab');
const btnJenisBarang  = document.getElementById('btnJenisBarang');

const labSelectGroup    = document.getElementById('labSelectGroup');
const barangSelectGroup = document.getElementById('barangSelectGroup');
const cardKursi         = document.getElementById('cardKursi');
const cardBarangInfo    = document.getElementById('cardBarangInfo');

const selectLab      = document.getElementById('selectLab');
const selectBarang    = document.getElementById('selectBarang');
const inputJumlah     = document.getElementById('inputJumlah');
const jumlahHint       = document.getElementById('jumlahHint');

const inputTanggal   = document.getElementById('inputTanggal');
const inputMulai     = document.getElementById('inputMulai');
const inputSelesai   = document.getElementById('inputSelesai');

const seatEmptyHint  = document.getElementById('seatEmptyHint');
const seatCardBody   = document.getElementById('seatCardBody');
const seatRows       = document.getElementById('seatRows');
const seatLoading    = document.getElementById('seatLoading');
const seatCountHint  = document.getElementById('seatCountHint');
const inputKursiPilih = document.getElementById('inputKursiPilih');

const barangEmptyHint  = document.getElementById('barangEmptyHint');
const barangPickerBody = document.getElementById('barangPickerBody');
const barangInfoNama   = document.getElementById('barangInfoNama');
const barangInfoLab    = document.getElementById('barangInfoLab');

const rowSumLab    = document.getElementById('rowSumLab');
const rowSumKursi  = document.getElementById('rowSumKursi');
const rowSumBarang = document.getElementById('rowSumBarang');
const rowSumJumlah = document.getElementById('rowSumJumlah');

let selectedSeat = null;
let fetchToken   = 0;
let currentJenis = 'lab';

/* ===== Toggle Jenis Peminjaman ===== */
function setJenis(jenis) {
    currentJenis = jenis;
    inputJenis.value = jenis;

    btnJenisLab.classList.toggle('active', jenis === 'lab');
    btnJenisBarang.classList.toggle('active', jenis === 'barang');

    const isLab = jenis === 'lab';

    labSelectGroup.style.display    = isLab ? 'block' : 'none';
    barangSelectGroup.style.display = isLab ? 'none'  : 'block';
    cardKursi.style.display         = isLab ? 'block' : 'none';
    cardBarangInfo.style.display    = isLab ? 'none'  : 'block';

    document.getElementById('sumJenis').textContent = isLab ? 'Ruang Lab' : 'Barang / Alat';
    rowSumLab.style.display    = isLab ? 'flex' : 'none';
    rowSumKursi.style.display  = isLab ? 'flex' : 'none';
    rowSumBarang.style.display = isLab ? 'none' : 'flex';
    rowSumJumlah.style.display = isLab ? 'none' : 'flex';

    updateSummary();
}

btnJenisLab.addEventListener('click', () => setJenis('lab'));
btnJenisBarang.addEventListener('click', () => setJenis('barang'));

/* ===== Seat map (jenis = lab) ===== */
function renderSeats(total, bookedSeats) {
    seatRows.innerHTML = '';
    selectedSeat = null;
    inputKursiPilih.value = '';
    updateSummary();

    seatCountHint.textContent = total ? total + ' kursi' : '';

    if (!total || total <= 0) {
        seatRows.innerHTML = '<div class="seat-empty-hint" style="padding:10px 0">Jumlah kursi lab ini belum diatur.</div>';
        return;
    }

    for (let i = 1; i <= total; i++) {
        const seat = document.createElement('div');
        seat.className = 'seat';
        seat.textContent = i;
        seat.dataset.seat = i;

        if (bookedSeats.includes(i)) {
            seat.classList.add('taken');
            seat.title = 'Kursi ' + i + ' sudah dipesan';
        } else {
            seat.title = 'Kursi ' + i;
            seat.addEventListener('click', () => selectSeat(i, seat));
        }

        seatRows.appendChild(seat);
    }
}

function selectSeat(num, el) {
    document.querySelectorAll('.seat.selected').forEach(s => s.classList.remove('selected'));
    el.classList.add('selected');
    selectedSeat = num;
    inputKursiPilih.value = num;
    updateSummary();
}

function loadBookedSeats() {
    const lab   = selectLab.value;
    const total = labsData[lab] || 0;

    if (!lab) {
        seatEmptyHint.style.display = 'block';
        seatCardBody.style.display  = 'none';
        return;
    }

    seatEmptyHint.style.display = 'none';
    seatCardBody.style.display  = 'block';

    const tanggal = inputTanggal.value;
    const mulai   = inputMulai.value;
    const selesai = inputSelesai.value;

    if (!tanggal || !mulai || !selesai) {
        renderSeats(total, []);
        return;
    }

    const token = ++fetchToken;
    seatLoading.style.display = 'block';
    seatRows.innerHTML = '';

    const params = new URLSearchParams({ nama_lab: lab, tanggal, jam_mulai: mulai, jam_selesai: selesai });

    fetch('../ajax/cek_kursi.php?' + params.toString())
        .then(res => res.json())
        .then(data => {
            if (token !== fetchToken) return;
            seatLoading.style.display = 'none';
            renderSeats(total, data.booked || []);
        })
        .catch(() => {
            if (token !== fetchToken) return;
            seatLoading.style.display = 'none';
            renderSeats(total, []);
        });
}

/* ===== Barang picker (jenis = barang) ===== */
function updateBarangInfo() {
    const id = selectBarang.value;

    if (!id || !barangData[id]) {
        barangEmptyHint.style.display  = 'block';
        barangPickerBody.style.display = 'none';
        inputJumlah.removeAttribute('max');
        jumlahHint.textContent = '';
        return;
    }

    const item = barangData[id];
    barangEmptyHint.style.display  = 'none';
    barangPickerBody.style.display = 'block';

    barangInfoNama.textContent = item.nama;
    barangInfoLab.textContent  = 'Lab: ' + item.nama_lab + ' · Stok tersedia: ' + item.stok;

    inputJumlah.setAttribute('max', item.stok);
    if (parseInt(inputJumlah.value || '1', 10) > item.stok) {
        inputJumlah.value = item.stok;
    }
    jumlahHint.textContent = 'Maksimal ' + item.stok + ' unit.';

    updateSummary();
}

/* ===== Live summary ===== */
function updateSummary() {
    const tanggal = inputTanggal.value || '';
    const mulai   = inputMulai.value   || '';
    const selesai = inputSelesai.value || '';

    document.getElementById('sumTanggal').textContent = tanggal
        ? new Date(tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        : '—';
    document.getElementById('sumWaktu').textContent = mulai && selesai
        ? mulai + ' – ' + selesai
        : mulai || '—';

    if (currentJenis === 'lab') {
        document.getElementById('sumLab').textContent = selectLab.value || '—';
        const seatEl = document.getElementById('sumKursi');
        if (selectedSeat) {
            seatEl.textContent = 'Kursi ' + selectedSeat;
            seatEl.classList.add('seat-picked');
        } else {
            seatEl.textContent = '—';
            seatEl.classList.remove('seat-picked');
        }
    } else {
        const id = selectBarang.value;
        document.getElementById('sumBarang').textContent = (id && barangData[id]) ? barangData[id].nama : '—';
        document.getElementById('sumJumlah').textContent = inputJumlah.value ? inputJumlah.value + ' unit' : '—';
    }
}

selectLab.addEventListener('change', () => { loadBookedSeats(); updateSummary(); });
selectBarang.addEventListener('change', () => { updateBarangInfo(); });
inputJumlah.addEventListener('input', () => { updateSummary(); });
inputTanggal.addEventListener('change', () => { loadBookedSeats(); updateSummary(); });
inputMulai.addEventListener('change', () => { loadBookedSeats(); updateSummary(); });
inputSelesai.addEventListener('change', () => { loadBookedSeats(); updateSummary(); });

document.getElementById('loanForm').addEventListener('submit', function (e) {
    if (currentJenis === 'lab') {
        if (!selectLab.value) {
            e.preventDefault();
            alert('Silakan pilih laboratorium terlebih dahulu.');
            return;
        }
        if (!selectedSeat) {
            e.preventDefault();
            alert('Silakan pilih kursi terlebih dahulu.');
            return;
        }
    } else {
        if (!selectBarang.value) {
            e.preventDefault();
            alert('Silakan pilih barang terlebih dahulu.');
            return;
        }
        if (!inputJumlah.value || parseInt(inputJumlah.value, 10) <= 0) {
            e.preventDefault();
            alert('Jumlah barang harus lebih dari 0.');
            return;
        }
    }
});

/* Init */
setJenis('lab');
updateSummary();
</script>

</body>
</html>