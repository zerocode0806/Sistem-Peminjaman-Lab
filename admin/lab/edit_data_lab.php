<?php
require_once __DIR__ . '/../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: ../../index.php');
  exit;
}

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: data_lab.php'); exit; }
$id = mysqli_real_escape_string($koneksi, $id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lab = mysqli_real_escape_string($koneksi, $_POST['nama_lab']);
    $lokasi   = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $stok     = (int) $_POST['stok'];
    $status = $_POST['status'] === 'availabel'
    ? 'availabel'
    : 'not available';

    mysqli_query($koneksi, "UPDATE data_lab
        SET nama_lab = '$nama_lab', stok = '$stok', status = '$status', lokasi = '$lokasi'
        WHERE id_lab = '$id'");

    header('Location: data_lab.php');
    exit;
}

$result = mysqli_query($koneksi, "SELECT * FROM data_lab WHERE id_lab = '$id'");
$lab = mysqli_fetch_assoc($result);
if (!$lab) { header('Location: data_lab.php'); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Laboratorium – LabSystem</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --bg: #F7F7F5; --surface: #FFFFFF; --border: #E8E8E3; --text: #18181B;
    --muted: #8C8C8A; --accent: #1A1A1A; --red: #DC2626; --red-soft: #FEF2F2;
    --green: #16A34A; --green-soft: #F0FDF4; --radius: 10px;
}
body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); font-size: 14px; }
.wrap { max-width: 480px; margin: 48px auto; padding: 0 16px; }
.back-link {
    display: inline-flex; align-items: center; gap: 6px; color: var(--muted);
    text-decoration: none; font-size: 13px; margin-bottom: 16px;
}
.back-link:hover { color: var(--text); }
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.card-header { padding: 20px 24px; border-bottom: 1px solid var(--border); }
.card-header h1 { font-size: 17px; font-weight: 600; margin-bottom: 3px; }
.card-header p { font-size: 12.5px; color: var(--muted); }
.card-body { padding: 24px; }
.form-group { margin-bottom: 18px; }
.form-group label { display: block; font-size: 12.5px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
.form-control {
    width: 100%; padding: 9px 12px; border: 1px solid var(--border); border-radius: 7px;
    font-family: 'DM Sans', sans-serif; font-size: 13.5px; color: var(--text); outline: none;
    background: var(--surface);
}
.form-control:focus { border-color: var(--accent); }
.form-actions { display: flex; gap: 10px; margin-top: 24px; }
.btn-primary, .btn-secondary {
    flex: 1; padding: 10px 14px; border-radius: 7px; font-size: 13.5px; font-weight: 500;
    border: none; cursor: pointer; text-align: center; text-decoration: none;
}
.btn-primary { background: var(--accent); color: #fff; }
.btn-primary:hover { opacity: .85; }
.btn-secondary { background: var(--bg); color: var(--text); border: 1px solid var(--border); }
.btn-secondary:hover { background: #f0f0ee; }
</style>
</head>
<body>
<div class="wrap">
    <a href="data_lab.php" class="back-link"><i class="bi bi-arrow-left"></i> Kembali ke Data Laboratorium</a>
    <div class="card">
        <div class="card-header">
            <h1>Edit Laboratorium</h1>
            <p>Perbarui informasi laboratorium <?= htmlspecialchars($lab['nama_lab']); ?></p>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <label>Nama Laboratorium</label>
                    <input type="text" name="nama_lab" class="form-control"
                           value="<?= htmlspecialchars($lab['nama_lab']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Lokasi</label>
                    <input type="text" name="lokasi" class="form-control"
                           value="<?= htmlspecialchars($lab['lokasi']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Stok / Kuota</label>
                    <input type="number" name="stok" class="form-control"
                           value="<?= (int)$lab['stok']; ?>" min="0" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="availabel"
                            <?= $lab['status'] === 'availabel' ? 'selected' : ''; ?>>
                            Tersedia
                        </option>

                        <option value="not available"
                            <?= $lab['status'] === 'not available' ? 'selected' : ''; ?>>
                            Tidak Tersedia
                        </option>
                    </select>
                </div>
                <div class="form-actions">
                    <a href="data_lab.php" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>