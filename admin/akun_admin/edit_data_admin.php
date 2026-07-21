<?php
session_start();
require_once __DIR__ . '/../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: data_admin.php');
    exit;
}

$stmtAdmin = mysqli_prepare($koneksi, "SELECT id_admin, nama, email, username FROM admin WHERE id_admin = ?");
mysqli_stmt_bind_param($stmtAdmin, "i", $id);
mysqli_stmt_execute($stmtAdmin);
$admin = mysqli_stmt_get_result($stmtAdmin)->fetch_assoc();

if (!$admin) {
    header('Location: data_admin.php');
    exit;
}

$errors = [];
$form = $admin;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['nama']     = trim($_POST['nama'] ?? '');
    $form['email']    = trim($_POST['email'] ?? '');
    $form['username'] = trim($_POST['username'] ?? '');
    $password         = $_POST['password'] ?? '';
    $konfirmasi       = $_POST['konfirmasi_password'] ?? '';

    if ($form['nama'] === '') $errors[] = 'Nama wajib diisi.';
    if ($form['email'] === '') {
        $errors[] = 'Email wajib diisi.';
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if ($form['username'] === '') $errors[] = 'Username wajib diisi.';

    $gantiPassword = $password !== '' || $konfirmasi !== '';
    if ($gantiPassword) {
        if (strlen($password) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        } elseif ($password !== $konfirmasi) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        }
    }

    // Cek email / username sudah dipakai admin LAIN
    if (empty($errors)) {
        $stmtCek = mysqli_prepare($koneksi, "SELECT id_admin FROM admin WHERE (email = ? OR username = ?) AND id_admin != ? LIMIT 1");
        mysqli_stmt_bind_param($stmtCek, "ssi", $form['email'], $form['username'], $id);
        mysqli_stmt_execute($stmtCek);
        if (mysqli_stmt_get_result($stmtCek)->fetch_assoc()) {
            $errors[] = 'Email atau username sudah digunakan admin lain.';
        }
    }

    if (empty($errors)) {
        if ($gantiPassword) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, "UPDATE admin SET nama = ?, email = ?, username = ?, password = ? WHERE id_admin = ?");
            mysqli_stmt_bind_param($stmt, "ssssi", $form['nama'], $form['email'], $form['username'], $hashed, $id);
        } else {
            $stmt = mysqli_prepare($koneksi, "UPDATE admin SET nama = ?, email = ?, username = ? WHERE id_admin = ?");
            mysqli_stmt_bind_param($stmt, "sssi", $form['nama'], $form['email'], $form['username'], $id);
        }

        if (mysqli_stmt_execute($stmt)) {
            // Segarkan sesi jika admin sedang mengedit akunnya sendiri
            if (isset($_SESSION['user']['id_admin']) && (int)$_SESSION['user']['id_admin'] === $id) {
                $_SESSION['user']['nama'] = $form['nama'];
            }
            header('Location: data_admin.php');
            exit;
        } else {
            $errors[] = 'Gagal menyimpan perubahan: ' . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Admin – LabSystem</title>

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

.form-header { margin-bottom: 20px; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.form-header h1 { font-size: 20px; font-weight: 600; letter-spacing: -.3px; margin-bottom: 4px; }
.form-header p { font-size: 13px; color: var(--muted); }

.id-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: 'DM Mono', monospace;
    font-size: 11.5px;
    color: var(--muted);
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 5px 10px;
    border-radius: 100px;
    white-space: nowrap;
}

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

.section-divider { height: 1px; background: var(--border); margin: 18px 0; }
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
        <span class="current">Edit Admin</span>
    </div>

    <div class="form-header">
        <div>
            <h1>Edit Admin</h1>
            <p>Perbarui data akun administrator</p>
        </div>
        <span class="id-chip"><i class="bi bi-hash"></i> <?= (int)$id ?></span>
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

        <form method="POST" action="edit_data_admin.php?id=<?= (int)$id ?>">

            <div class="field-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($form['nama']); ?>" required>
            </div>

            <div class="field-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($form['email']); ?>" required>
            </div>

            <div class="field-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($form['username']); ?>" required>
            </div>

            <div class="section-divider"></div>

            <div class="field-row">
                <div class="field-group">
                    <label for="password">Password Baru</label>
                    <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div class="field-group">
                    <label for="konfirmasi_password">Konfirmasi Password Baru</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi password baru">
                </div>
            </div>
            <p class="hint">Biarkan kosong jika tidak ingin mengubah password.</p>

            <div class="form-actions">
                <a href="data_admin.php" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>