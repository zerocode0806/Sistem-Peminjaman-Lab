<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    if ($nama === '') $errors[] = 'Nama wajib diisi.';
    if ($email === '') {
        $errors[] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if ($username === '') $errors[] = 'Username wajib diisi.';
    if ($password === '') {
        $errors[] = 'Password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirmasi) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    // Cek email / username sudah dipakai
    if (empty($errors)) {
        $stmtCek = mysqli_prepare($koneksi, "SELECT id_admin FROM admin WHERE email = ? OR username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmtCek, "ss", $email, $username);
        mysqli_stmt_execute($stmtCek);
        if (mysqli_stmt_get_result($stmtCek)->fetch_assoc()) {
            $errors[] = 'Email atau username sudah digunakan admin lain.';
        }
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($koneksi, "INSERT INTO admin (nama, email, username, password) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $username, $hashed);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: data_admin.php');
            exit;
        } else {
            $errors[] = 'Gagal menyimpan data: ' . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tambah Admin – LabSystem</title>

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
    align-items: flex-start;
    justify-content: center;
    padding: 48px 20px;
}

.form-page { width: 100%; max-width: 480px; }

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    color: var(--muted);
    margin-bottom: 14px;
}
.breadcrumb a { color: var(--muted); text-decoration: none; }
.breadcrumb a:hover { color: var(--text); }
.breadcrumb i { font-size: 10px; }
.breadcrumb .current { color: var(--text); font-weight: 500; }

.form-header { margin-bottom: 20px; }
.form-header h1 { font-size: 20px; font-weight: 600; letter-spacing: -.3px; margin-bottom: 4px; }
.form-header p { font-size: 13px; color: var(--muted); }

.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
}

.alert-error {
    background: var(--red-soft);
    color: var(--red);
    border: 1px solid #FCA5A5;
    border-radius: 8px;
    padding: 12px 14px;
    font-size: 13px;
    margin-bottom: 18px;
}
.alert-error ul { margin: 4px 0 0 18px; }

.field-group { margin-bottom: 16px; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

label {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 6px;
}

input[type="text"],
input[type="email"],
input[type="password"] {
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

input:focus {
    border-color: var(--accent);
    background: var(--surface);
}

.hint { font-size: 11.5px; color: var(--muted); margin-top: 5px; }

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 24px;
    padding-top: 18px;
    border-top: 1px solid var(--border);
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    background: var(--accent);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    border: none;
    border-radius: 7px;
    cursor: pointer;
    transition: opacity .15s;
}
.btn-primary:hover { opacity: .85; }

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    background: var(--surface);
    color: var(--text);
    font-size: 13px;
    font-weight: 500;
    border: 1px solid var(--border);
    border-radius: 7px;
    text-decoration: none;
    cursor: pointer;
    transition: background .15s;
}
.btn-secondary:hover { background: var(--bg); }

@media (max-width: 480px) {
    .field-row { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<div class="form-page">

    <div class="breadcrumb">
        <a href="data_admin.php">Data Admin</a>
        <i class="bi bi-chevron-right"></i>
        <span class="current">Tambah Admin</span>
    </div>

    <div class="form-header">
        <h1>Tambah Admin</h1>
        <p>Buat akun administrator baru untuk mengakses panel ini</p>
    </div>

    <div class="card">
        <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <strong>Terjadi kesalahan:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="tambah_admin.php">

            <div class="field-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" placeholder="Nama lengkap"
                       value="<?= htmlspecialchars($_POST['nama'] ?? ''); ?>" required>
            </div>

            <div class="field-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="nama@contoh.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>

            <div class="field-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Username untuk login"
                       value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>

            <div class="field-row">
                <div class="field-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                </div>
                <div class="field-group">
                    <label for="konfirmasi_password">Konfirmasi Password</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi password" required>
                </div>
            </div>
            <p class="hint">Password akan disimpan dalam bentuk terenkripsi (hash).</p>

            <div class="form-actions">
                <a href="data_admin.php" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check2"></i> Simpan Admin
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>