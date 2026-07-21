<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

$errorLogin    = '';
$errorSignup   = '';
$successSignup = '';
$activeForm    = 'login';

/* ===============================
   LOGIN MAHASISWA
================================ */
if (isset($_POST['login'])) {
    $activeForm = 'login';

    $nim      = trim($_POST['nim'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nim === '' || $password === '') {
        $errorLogin = "NIM dan password wajib diisi!";
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM mahasiswa WHERE nim = ?");
        mysqli_stmt_bind_param($stmt, "s", $nim);
        mysqli_stmt_execute($stmt);
        $mhs = mysqli_stmt_get_result($stmt)->fetch_assoc();

        if ($mhs && password_verify($password, $mhs['password'])) {
            unset($mhs['password']);
            $_SESSION['mahasiswa'] = $mhs;
            header("Location: ../mahasiswa/dashboard_mhs.php");
            exit;
        } else {
            $errorLogin = "NIM atau password salah!";
        }
    }
}

/* ===============================
   SIGNUP MAHASISWA
================================ */
if (isset($_POST['signup'])) {
    $activeForm = 'signup';

    $nama       = trim($_POST['nama'] ?? '');
    $nim        = trim($_POST['nim'] ?? '');
    $telepon    = trim($_POST['telepon'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');
    $password   = $_POST['password'] ?? '';
    $konfirmasi = $_POST['confirm_password'] ?? '';

    if ($nama === '' || $nim === '' || $telepon === '' || $alamat === '' || $password === '') {
        $errorSignup = "Semua kolom wajib diisi.";
    } elseif (strlen($password) < 6) {
        $errorSignup = "Password minimal 6 karakter.";
    } elseif ($password !== $konfirmasi) {
        $errorSignup = "Konfirmasi password tidak cocok.";
    } else {
        $stmtCek = mysqli_prepare($koneksi, "SELECT nim FROM mahasiswa WHERE nim = ?");
        mysqli_stmt_bind_param($stmtCek, "s", $nim);
        mysqli_stmt_execute($stmtCek);

        if (mysqli_stmt_get_result($stmtCek)->fetch_assoc()) {
            $errorSignup = "NIM sudah digunakan!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmtInsert = mysqli_prepare($koneksi, "
                INSERT INTO mahasiswa (nama, nim, no_telepon, alamat, password)
                VALUES (?, ?, ?, ?, ?)
            ");
            mysqli_stmt_bind_param($stmtInsert, "sssss", $nama, $nim, $telepon, $alamat, $hashed);

            if (mysqli_stmt_execute($stmtInsert)) {
                $successSignup = "Pendaftaran berhasil! Silakan masuk.";
                $activeForm = 'login';
            } else {
                $errorSignup = "Gagal mendaftar: " . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk Mahasiswa – LabSystem</title>

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
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

/* ── PAGE ── */
.auth-page { width: 100%; max-width: 420px; }

/* ── BRAND ── */
.brand {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 28px;
}

.brand-icon {
    width: 36px; height: 36px;
    background: var(--accent);
    border-radius: 9px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.brand-icon i { color: #fff; font-size: 17px; }

.brand-text strong { display: block; font-size: 15px; font-weight: 600; color: var(--text); }
.brand-text span { font-size: 11.5px; color: var(--muted); }

/* ── SEGMENTED TOGGLE ── */
.segmented {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 4px;
    margin-bottom: 20px;
}

.segmented-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 9px;
    background: transparent;
    border: none;
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--muted);
    cursor: pointer;
    transition: background .15s, color .15s;
}

.segmented-btn i { font-size: 14px; }

.segmented-btn.active {
    background: var(--surface);
    color: var(--text);
    box-shadow: 0 1px 2px rgba(0,0,0,.06);
}

/* ── CARD ── */
.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
}

.card-header { margin-bottom: 20px; }
.card-header h1 { font-size: 18px; font-weight: 600; letter-spacing: -.3px; margin-bottom: 3px; }
.card-header p { font-size: 13px; color: var(--muted); }

/* ── ALERTS ── */
.alert-error {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    background: var(--red-soft);
    border: 1px solid #FECACA;
    border-radius: 8px;
    padding: 11px 14px;
    margin-bottom: 18px;
    font-size: 13px;
    color: var(--red);
}
.alert-error i { font-size: 15px; flex-shrink: 0; margin-top: 1px; }

.alert-success {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    background: var(--green-soft);
    border: 1px solid #BBF7D0;
    border-radius: 8px;
    padding: 11px 14px;
    margin-bottom: 18px;
    font-size: 13px;
    color: var(--green);
    font-weight: 500;
}
.alert-success i { font-size: 15px; flex-shrink: 0; margin-top: 1px; }

/* ── FORM ── */
.field-group { margin-bottom: 16px; }
.field-group:last-of-type { margin-bottom: 0; }

.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

label {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 6px;
}

input[type="text"],
input[type="tel"],
input[type="password"] {
    width: 100%;
    padding: 10px 12px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13.5px;
    color: var(--text);
    outline: none;
    transition: border-color .15s, background .15s;
}

input:focus {
    border-color: var(--accent);
    background: var(--surface);
    box-shadow: 0 0 0 3px rgba(26,26,26,.06);
}

.password-wrap { position: relative; }
.password-wrap input { padding-right: 40px; }

.toggle-eye {
    position: absolute;
    right: 4px;
    top: 4px;
    width: 32px; height: 32px;
    display: grid;
    place-items: center;
    background: transparent;
    border: none;
    border-radius: 6px;
    color: var(--muted);
    cursor: pointer;
    transition: background .15s, color .15s;
}
.toggle-eye:hover { background: var(--bg); color: var(--text); }
.toggle-eye i { font-size: 14px; }

/* ── BUTTON ── */
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
    margin-top: 20px;
}

.btn-submit:hover { opacity: .85; }

.form-footer-hint {
    text-align: center;
    margin-top: 16px;
    font-size: 12px;
    color: var(--muted);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12.5px;
    color: var(--muted);
    text-decoration: none;
    margin-bottom: 16px;
    transition: color .15s;
}
.back-link:hover { color: var(--text); }

.hidden { display: none; }

@media (max-width: 420px) {
    .card { padding: 20px; }
    .field-row { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<div class="auth-page">

    <a href="../index.php" class="back-link">
        <i class="bi bi-arrow-left"></i> Kembali ke beranda
    </a>

    <div class="brand">
        <div class="brand-icon"><i class="bi bi-boxes"></i></div>
        <div class="brand-text">
            <strong>LabSystem</strong>
            <span>Portal Mahasiswa</span>
        </div>
    </div>

    <div class="segmented">
        <button type="button" class="segmented-btn <?= $activeForm === 'login' ? 'active' : '' ?>" id="tabLogin" onclick="showLogin()">
            <i class="bi bi-box-arrow-in-right"></i> Masuk
        </button>
        <button type="button" class="segmented-btn <?= $activeForm === 'signup' ? 'active' : '' ?>" id="tabSignup" onclick="showSignup()">
            <i class="bi bi-person-plus"></i> Daftar
        </button>
    </div>

    <!-- LOGIN CARD -->
    <div class="card <?= $activeForm === 'login' ? '' : 'hidden' ?>" id="loginForm">
        <div class="card-header">
            <h1>Masuk</h1>
            <p>Gunakan akun mahasiswa Anda</p>
        </div>

        <?php if ($successSignup): ?>
        <div class="alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <span><?= htmlspecialchars($successSignup) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($errorLogin): ?>
        <div class="alert-error">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span><?= htmlspecialchars($errorLogin) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="field-group">
                <label for="loginNim">NIM</label>
                <input type="text" id="loginNim" name="nim" placeholder="Masukkan NIM" inputmode="numeric" required>
            </div>
            <div class="field-group">
                <label for="loginPassword">Password</label>
                <div class="password-wrap">
                    <input type="password" id="loginPassword" name="password" placeholder="••••••••" required>
                    <button type="button" class="toggle-eye" onclick="togglePassword('loginPassword', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" name="login" class="btn-submit">
                <i class="bi bi-box-arrow-in-right"></i> Masuk
            </button>
        </form>

        <p class="form-footer-hint">Belum punya akun? <a href="#" onclick="showSignup(); return false;" style="color:var(--text);font-weight:600;text-decoration:none;">Daftar</a></p>
    </div>

    <!-- SIGNUP CARD -->
    <div class="card <?= $activeForm === 'signup' ? '' : 'hidden' ?>" id="signupForm">
        <div class="card-header">
            <h1>Daftar</h1>
            <p>Buat akun mahasiswa baru</p>
        </div>

        <?php if ($errorSignup): ?>
        <div class="alert-error">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span><?= htmlspecialchars($errorSignup) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="field-group">
                <label for="signupNama">Nama Lengkap</label>
                <input type="text" id="signupNama" name="nama" placeholder="Nama lengkap"
                       value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
            </div>

            <div class="field-row">
                <div class="field-group">
                    <label for="signupNim">NIM</label>
                    <input type="text" id="signupNim" name="nim" placeholder="Nomor Induk Mahasiswa" inputmode="numeric"
                           value="<?= htmlspecialchars($_POST['nim'] ?? '') ?>" required>
                </div>
                <div class="field-group">
                    <label for="signupTelepon">No Telepon</label>
                    <input type="tel" id="signupTelepon" name="telepon" placeholder="08xxxxxxxxxx"
                           value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>" required>
                </div>
            </div>

            <div class="field-group">
                <label for="signupAlamat">Alamat</label>
                <input type="text" id="signupAlamat" name="alamat" placeholder="Alamat tempat tinggal"
                       value="<?= htmlspecialchars($_POST['alamat'] ?? '') ?>" required>
            </div>

            <div class="field-group">
                <label for="signupPassword">Password</label>
                <div class="password-wrap">
                    <input type="password" id="signupPassword" name="password" placeholder="Minimal 6 karakter" minlength="6" required>
                    <button type="button" class="toggle-eye" onclick="togglePassword('signupPassword', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="field-group">
                <label for="confirmPassword">Konfirmasi Password</label>
                <div class="password-wrap">
                    <input type="password" id="confirmPassword" name="confirm_password" placeholder="Ulangi password" minlength="6" required>
                    <button type="button" class="toggle-eye" onclick="togglePassword('confirmPassword', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" name="signup" class="btn-submit">
                <i class="bi bi-person-plus"></i> Daftar
            </button>
        </form>

        <p class="form-footer-hint">Sudah punya akun? <a href="#" onclick="showLogin(); return false;" style="color:var(--text);font-weight:600;text-decoration:none;">Masuk</a></p>
    </div>

</div>

<script>
function showSignup() {
    document.getElementById("loginForm").classList.add("hidden");
    document.getElementById("signupForm").classList.remove("hidden");
    document.getElementById("tabLogin").classList.remove("active");
    document.getElementById("tabSignup").classList.add("active");
}

function showLogin() {
    document.getElementById("signupForm").classList.add("hidden");
    document.getElementById("loginForm").classList.remove("hidden");
    document.getElementById("tabSignup").classList.remove("active");
    document.getElementById("tabLogin").classList.add("active");
}

function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector("i");
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    }
}
</script>

</body>
</html>