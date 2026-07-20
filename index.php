<?php
session_start();

// Kalau sudah login, langsung arahkan ke dashboard masing-masing
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}
if (isset($_SESSION['mahasiswa'])) {
    header('Location: dashboard_mhs.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>LabSystem – Pilih Akun</title>

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
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

.landing-page { width: 100%; max-width: 760px; }

/* ── BRAND ── */
.brand {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 12px;
}

.brand-icon {
    width: 40px; height: 40px;
    background: var(--accent);
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.brand-icon i { color: #fff; font-size: 19px; }

.brand-text strong { display: block; font-size: 17px; font-weight: 600; color: var(--text); }
.brand-text span { font-size: 12px; color: var(--muted); }

/* ── INTRO ── */
.intro {
    text-align: center;
    margin-bottom: 36px;
}

.intro h1 {
    font-size: 22px;
    font-weight: 600;
    letter-spacing: -.3px;
    margin-bottom: 6px;
}

.intro p { font-size: 13.5px; color: var(--muted); }

/* ── ROLE CARDS ── */
.role-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.role-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 28px 24px;
    text-decoration: none;
    color: var(--text);
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
    transition: border-color .18s, transform .18s, box-shadow .18s;
}

.role-card:hover {
    border-color: var(--accent);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,.06);
}

.role-icon {
    width: 46px; height: 46px;
    border-radius: 11px;
    display: grid;
    place-items: center;
    font-size: 21px;
    flex-shrink: 0;
}

.role-icon.mhs    { background: var(--blue-soft);   color: var(--blue); }
.role-icon.admin  { background: var(--violet-soft); color: var(--violet); }

.role-card h2 {
    font-size: 15.5px;
    font-weight: 600;
    color: var(--text);
}

.role-card p {
    font-size: 12.5px;
    color: var(--muted);
    line-height: 1.5;
}

.role-cta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text);
    margin-top: auto;
    padding-top: 6px;
}

.role-cta i { font-size: 12px; transition: transform .18s; }
.role-card:hover .role-cta i { transform: translateX(3px); }

/* ── FOOTER NOTE ── */
.footer-note {
    text-align: center;
    margin-top: 28px;
    font-size: 12px;
    color: var(--muted);
}

@media (max-width: 560px) {
    .role-grid { grid-template-columns: 1fr; }
    .intro h1 { font-size: 19px; }
}
</style>
</head>
<body>

<div class="landing-page">

    <div class="brand">
        <div class="brand-icon"><i class="bi bi-boxes"></i></div>
        <div class="brand-text">
            <strong>LabSystem</strong>
            <span>Sistem Peminjaman Laboratorium</span>
        </div>
    </div>

    <div class="intro">
        <h1>Selamat datang</h1>
        <p>Pilih jenis akun untuk melanjutkan</p>
    </div>

    <div class="role-grid">
        <a href="login_mhs.php" class="role-card">
            <div class="role-icon mhs"><i class="bi bi-mortarboard-fill"></i></div>
            <div>
                <h2>Mahasiswa</h2>
                <p>Ajukan peminjaman ruang laboratorium atau barang/alat, dan pantau status pengajuan Anda.</p>
            </div>
            <span class="role-cta">Masuk sebagai Mahasiswa <i class="bi bi-arrow-right"></i></span>
        </a>

        <a href="login_admin.php" class="role-card">
            <div class="role-icon admin"><i class="bi bi-shield-lock-fill"></i></div>
            <div>
                <h2>Admin</h2>
                <p>Kelola data laboratorium, barang, mahasiswa, dan proses persetujuan peminjaman.</p>
            </div>
            <span class="role-cta">Masuk sebagai Admin <i class="bi bi-arrow-right"></i></span>
        </a>
    </div>

    <p class="footer-note">Belum punya akun? Pilih peran di atas — halaman masuk juga menyediakan opsi pendaftaran.</p>

</div>

</body>
</html>