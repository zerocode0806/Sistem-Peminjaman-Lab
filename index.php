<?php
session_start();

// Kalau sudah login, langsung arahkan ke dashboard masing-masing
if (isset($_SESSION['user'])) {
    header('Location: admin/dashboard.php');
    exit;
}
if (isset($_SESSION['mahasiswa'])) {
    header('Location: mahasiswa/dashboard_mhs.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>LabSystem – Reservasi Laboratorium</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg:          #F7F7F5;
    --surface:     #FFFFFF;
    --border:      #E8E8E3;
    --text:        #18181B;
    --muted:       #8C8C8A;
    --accent:      #1A1A1A;
    --blue:        #2563EB;
    --blue-soft:   #EFF4FF;
    --violet:      #7C3AED;
    --violet-soft: #F5F3FF;
    --red:         #DC2626;
    --red-soft:    #FEF2F2;
    --green:       #16A34A;
    --radius:      10px;
}

html { scroll-behavior: smooth; }

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    font-size: 14px;
    line-height: 1.5;
    min-height: 100vh;
    overflow-x: hidden;
}

.display {
    font-family: 'Space Grotesk', sans-serif;
    letter-spacing: -.02em;
}

.mono {
    font-family: 'DM Mono', monospace;
}

/* ── PAGE SHELL ── */
.shell {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1.05fr 0.95fr;
}

/* ── LEFT: CONTENT ── */
.content-col {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 56px 64px;
    position: relative;
}

.brand {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 56px;
}

.brand-icon {
    width: 34px; height: 34px;
    background: var(--accent);
    border-radius: 9px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.brand-icon i { color: #fff; font-size: 16px; }

.brand-text strong { display: block; font-size: 14px; font-weight: 600; color: var(--text); }
.brand-text span { font-size: 10.5px; color: var(--muted); letter-spacing: .04em; }

.content-inner { max-width: 480px; }

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'DM Mono', monospace;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: .08em;
    color: var(--muted);
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 5px 11px;
    border-radius: 100px;
    margin-bottom: 22px;
}

.eyebrow .dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--green);
    flex-shrink: 0;
}

.intro h1 {
    font-size: 38px;
    font-weight: 600;
    letter-spacing: -.02em;
    line-height: 1.12;
    margin-bottom: 16px;
}

.intro h1 em {
    font-style: normal;
    color: var(--blue);
}

.intro p {
    font-size: 14.5px;
    color: var(--muted);
    line-height: 1.65;
    margin-bottom: 36px;
    max-width: 420px;
}

/* ── ROLE CARDS ── */
.role-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 32px;
}

.role-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 22px 20px;
    text-decoration: none;
    color: var(--text);
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
    transition: border-color .18s, transform .18s, box-shadow .18s;
    position: relative;
    overflow: hidden;
}

.role-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: var(--role-color, var(--accent));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform .22s ease;
}

.role-card:hover {
    border-color: var(--role-color, var(--accent));
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(24,24,27,.07);
}

.role-card:hover::before { transform: scaleX(1); }

.role-card.mhs   { --role-color: var(--blue); }
.role-card.admin { --role-color: var(--violet); }

.role-icon {
    width: 38px; height: 38px;
    border-radius: 9px;
    display: grid;
    place-items: center;
    font-size: 17px;
    flex-shrink: 0;
}

.role-icon.mhs    { background: var(--blue-soft);   color: var(--blue); }
.role-icon.admin  { background: var(--violet-soft); color: var(--violet); }

.role-card h2 {
    font-size: 14.5px;
    font-weight: 600;
    color: var(--text);
}

.role-card p {
    font-size: 12px;
    color: var(--muted);
    line-height: 1.5;
}

.role-cta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--role-color, var(--text));
    margin-top: auto;
    padding-top: 4px;
}

.role-cta i { font-size: 11px; transition: transform .18s; }
.role-card:hover .role-cta i { transform: translateX(3px); }

/* ── FEATURE STRIP ── */
.feature-strip {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding-top: 24px;
    border-top: 1px solid var(--border);
}

.feature-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.feature-row i {
    font-size: 13px;
    color: var(--muted);
    margin-top: 2px;
    flex-shrink: 0;
    width: 16px;
}

.feature-row .feature-text strong {
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text);
    display: block;
    margin-bottom: 1px;
}

.feature-row .feature-text span {
    font-size: 12px;
    color: var(--muted);
}

/* ── RIGHT: SEAT MAP PANEL ── */
.visual-col {
    position: relative;
    background: linear-gradient(180deg, #101012 0%, #17171A 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px;
    overflow: hidden;
}

.visual-col::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
    background-size: 28px 28px;
    pointer-events: none;
}

.panel-glow {
    position: absolute;
    width: 480px; height: 480px;
    background: radial-gradient(circle, rgba(37,99,235,.18) 0%, transparent 68%);
    top: -120px;
    right: -140px;
    pointer-events: none;
}

.room-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 360px;
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 16px;
    padding: 28px 24px 24px;
    backdrop-filter: blur(6px);
}

.room-card-label {
    font-family: 'DM Mono', monospace;
    font-size: 10.5px;
    letter-spacing: .08em;
    color: rgba(255,255,255,.4);
    text-transform: uppercase;
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.room-card-label .live-dot {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #4ADE80;
}

.room-card-label .live-dot::before {
    content: '';
    width: 5px; height: 5px;
    border-radius: 50%;
    background: #4ADE80;
    box-shadow: 0 0 0 0 rgba(74,222,128,.6);
    animation: livePulse 2s ease-out infinite;
}

@keyframes livePulse {
    0%   { box-shadow: 0 0 0 0 rgba(74,222,128,.5); }
    70%  { box-shadow: 0 0 0 6px rgba(74,222,128,0); }
    100% { box-shadow: 0 0 0 0 rgba(74,222,128,0); }
}

.admin-desk-mini {
    display: flex;
    justify-content: center;
    margin-bottom: 22px;
}

.admin-desk-mini span {
    font-family: 'DM Mono', monospace;
    font-size: 9.5px;
    font-weight: 500;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: rgba(255,255,255,.55);
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.1);
    padding: 6px 18px;
    border-radius: 6px 6px 2px 2px;
}

.mini-seat-rows {
    display: flex;
    flex-direction: row-reverse;
    flex-wrap: wrap;
    gap: 7px;
    justify-content: center;
    margin-bottom: 22px;
}

.mini-seat {
    width: 24px; height: 24px;
    border-radius: 5px 5px 2px 2px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.1);
    animation: seatCycle 7s ease-in-out infinite;
    animation-delay: calc(var(--i) * 0.35s);
}

@keyframes seatCycle {
    0%, 55%, 100% {
        background: rgba(255,255,255,.05);
        border-color: rgba(255,255,255,.1);
    }
    65%, 85% {
        background: rgba(37,99,235,.9);
        border-color: rgba(37,99,235,.9);
    }
}

.room-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 18px;
    border-top: 1px solid rgba(255,255,255,.08);
}

.room-card-footer .rc-lab {
    font-size: 12.5px;
    font-weight: 600;
    color: #fff;
}

.room-card-footer .rc-lab span {
    display: block;
    font-family: 'DM Mono', monospace;
    font-size: 10.5px;
    font-weight: 400;
    color: rgba(255,255,255,.4);
    margin-top: 2px;
}

.rc-badge {
    font-family: 'DM Mono', monospace;
    font-size: 10px;
    font-weight: 500;
    color: #4ADE80;
    background: rgba(74,222,128,.12);
    border: 1px solid rgba(74,222,128,.25);
    padding: 4px 9px;
    border-radius: 100px;
}

.visual-caption {
    position: absolute;
    bottom: 40px;
    left: 48px;
    right: 48px;
    z-index: 1;
    text-align: center;
}

.visual-caption p {
    font-size: 12px;
    color: rgba(255,255,255,.4);
    line-height: 1.6;
}

/* ── RESPONSIVE ── */
@media (max-width: 980px) {
    .shell { grid-template-columns: 1fr; }
    .visual-col { display: none; }
    .content-col { padding: 44px 28px; min-height: 100vh; }
}

@media (max-width: 520px) {
    .intro h1 { font-size: 29px; }
    .role-grid { grid-template-columns: 1fr; }
    .brand { margin-bottom: 40px; }
}

@media (prefers-reduced-motion: reduce) {
    .mini-seat { animation: none; }
    .eyebrow .dot,
    .room-card-label .live-dot::before { animation: none; }
    * { transition: none !important; }
}
</style>
</head>
<body>

<div class="shell">

    <!-- LEFT: Content -->
    <div class="content-col">
        <div class="content-inner">

            <div class="brand">
                <div class="brand-icon"><i class="bi bi-boxes"></i></div>
                <div class="brand-text">
                    <strong>LabSystem</strong>
                    <span>PROGRAM STUDI INFORMATIKA</span>
                </div>
            </div>

            <div class="intro">
                <span class="eyebrow"><span class="dot"></span> Reservasi lab real-time</span>
                <h1>Pesan kursi lab,<br>bukan <em>rebutan tempat</em>.</h1>
                <p>Pilih laboratorium, jadwal, dan kursi Anda sendiri lewat denah interaktif — lalu pantau status pengajuan tanpa harus bolak-balik ke ruang admin.</p>
            </div>

            <div class="role-grid">
                <a href="auth/login_mhs.php" class="role-card mhs">
                    <div class="role-icon mhs"><i class="bi bi-mortarboard-fill"></i></div>
                    <div>
                        <h2>Mahasiswa</h2>
                        <p>Ajukan peminjaman lab lengkap dengan pilihan kursi, dan pantau status pengajuan Anda.</p>
                    </div>
                    <span class="role-cta">Masuk sebagai Mahasiswa <i class="bi bi-arrow-right"></i></span>
                </a>

                <a href="auth/login_admin.php" class="role-card admin">
                    <div class="role-icon admin"><i class="bi bi-shield-lock-fill"></i></div>
                    <div>
                        <h2>Admin</h2>
                        <p>Kelola data lab, kursi, inventaris, dan setujui pengajuan peminjaman.</p>
                    </div>
                    <span class="role-cta">Masuk sebagai Admin <i class="bi bi-arrow-right"></i></span>
                </a>
            </div>

            <div class="feature-strip">
                <div class="feature-row">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    <div class="feature-text">
                        <strong>Denah kursi interaktif</strong>
                        <span>Pilih kursi seperti reservasi bioskop — bukan asal klik lab kosong.</span>
                    </div>
                </div>
                <div class="feature-row">
                    <i class="bi bi-clock-history"></i>
                    <div class="feature-text">
                        <strong>Status yang bisa dipantau</strong>
                        <span>Menunggu, disetujui, atau ditolak — semua terlihat tanpa bertanya ke admin.</span>
                    </div>
                </div>
                <div class="feature-row">
                    <i class="bi bi-building"></i>
                    <div class="feature-text">
                        <strong>Satu portal, semua lab</strong>
                        <span>Akses ke seluruh laboratorium fakultas dari satu tempat.</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- RIGHT: Decorative seat map -->
    <div class="visual-col">
        <div class="panel-glow"></div>

        <div class="room-card">
            <div class="room-card-label">
                <span>Lab.Sys / Live Preview</span>
                <span class="live-dot">Live</span>
            </div>

            <div class="admin-desk-mini">
                <span>Meja Admin</span>
            </div>

            <div class="mini-seat-rows" id="miniSeatRows"></div>

            <div class="room-card-footer">
                <div class="rc-lab">
                    Algoritma &amp; Pemrograman
                    <span>Gedung Saintek · Lt. 2</span>
                </div>
                <span class="rc-badge">Tersedia</span>
            </div>
        </div>

        <div class="visual-caption">
            <p>Setiap kursi punya statusnya sendiri — dipilih langsung oleh mahasiswa saat mengajukan peminjaman.</p>
        </div>
    </div>

</div>

<script>
// Bangun grid kursi mini secara acak agar animasinya tidak terasa seragam/kaku
const seatWrap = document.getElementById('miniSeatRows');
const totalSeats = 24;

for (let i = 1; i <= totalSeats; i++) {
    const seat = document.createElement('div');
    seat.className = 'mini-seat';
    seat.style.setProperty('--i', Math.floor(Math.random() * 10));
    seatWrap.appendChild(seat);
}
</script>

</body>
</html>