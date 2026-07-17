<?php
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

if (!isset($_GET['id']) || $_GET['id'] === '') {
  header('Location: data_barang.php');
  exit;
}

$id_barang = $_GET['id'];

$stmtBarang = mysqli_prepare($koneksi, "SELECT * FROM data_barang WHERE id_barang = ?");
mysqli_stmt_bind_param($stmtBarang, "s", $id_barang);
mysqli_stmt_execute($stmtBarang);
$barang = mysqli_stmt_get_result($stmtBarang)->fetch_assoc();

if (!$barang) {
  header('Location: data_barang.php');
  exit;
}

$errors = [];
$form = $barang; // nilai default form = data lama

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['id_lab']      = $_POST['id_lab'] ?? '';
    $form['kode_barang'] = trim($_POST['kode_barang'] ?? '');
    $form['nama_barang'] = trim($_POST['nama_barang'] ?? '');
    $form['kategori']    = trim($_POST['kategori'] ?? '');
    $form['stok']        = (int)($_POST['stok'] ?? 0);
    $form['kondisi']     = $_POST['kondisi'] ?? 'baik';
    $form['status']      = $_POST['status'] ?? 'availabel';
    $form['keterangan']  = trim($_POST['keterangan'] ?? '');

    if ($form['id_lab'] === '') $errors[] = 'Lab pemilik barang wajib dipilih.';
    if ($form['kode_barang'] === '') $errors[] = 'Kode barang wajib diisi.';
    if ($form['nama_barang'] === '') $errors[] = 'Nama barang wajib diisi.';
    if ($form['stok'] < 0) $errors[] = 'Stok tidak boleh bernilai negatif.';
    if (!in_array($form['kondisi'], ['baik','rusak','perbaikan'], true)) $errors[] = 'Kondisi tidak valid.';
    if (!in_array($form['status'], ['availabel','tidak availabel'], true)) $errors[] = 'Status tidak valid.';

    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi,
            "UPDATE data_barang
             SET id_lab = ?, kode_barang = ?, nama_barang = ?, kategori = ?, stok = ?, kondisi = ?, status = ?, keterangan = ?
             WHERE id_barang = ?");
        mysqli_stmt_bind_param($stmt, "ssssisssi",
            $form['id_lab'], $form['kode_barang'], $form['nama_barang'], $form['kategori'], $form['stok'],
            $form['kondisi'], $form['status'], $form['keterangan'], $id_barang);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: data_barang.php?id_lab=' . urlencode($form['id_lab']));
            exit;
        } else {
            $errors[] = 'Gagal menyimpan perubahan: ' . mysqli_error($koneksi);
        }
    }
}

$daftarLab = mysqli_query($koneksi, "SELECT id_lab, nama_lab FROM data_lab ORDER BY nama_lab ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Barang – LabSystem</title>

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

.form-page { width: 100%; max-width: 560px; }

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
input[type="number"],
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
}

input:focus, select:focus, textarea:focus {
    border-color: var(--accent);
    background: var(--surface);
}

textarea { resize: vertical; min-height: 80px; font-family: inherit; }

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-top: 24px;
    padding-top: 18px;
    border-top: 1px solid var(--border);
}

.form-actions-right { display: flex; gap: 10px; }

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

.btn-danger-outline {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    background: var(--red-soft);
    color: var(--red);
    font-size: 13px;
    font-weight: 500;
    border: 1px solid #FCA5A5;
    border-radius: 7px;
    text-decoration: none;
    cursor: pointer;
    transition: opacity .15s;
}
.btn-danger-outline:hover { opacity: .85; }

@media (max-width: 480px) {
    .field-row { grid-template-columns: 1fr; }
    .form-actions { flex-direction: column-reverse; align-items: stretch; }
    .form-actions-right { flex-direction: column; }
}
</style>
</head>
<body>

<div class="form-page">

    <div class="breadcrumb">
        <a href="data_barang.php">Data Barang</a>
        <i class="bi bi-chevron-right"></i>
        <span class="current">Edit Barang</span>
    </div>

    <div class="form-header">
        <div>
            <h1>Edit Barang</h1>
            <p>Perbarui data barang inventaris laboratorium</p>
        </div>
        <span class="id-chip"><i class="bi bi-hash"></i> <?= htmlspecialchars($id_barang); ?></span>
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

        <form method="POST" action="edit_data_barang.php?id=<?= urlencode($id_barang); ?>">

            <div class="field-group">
                <label for="id_lab">Lab Pemilik</label>
                <select id="id_lab" name="id_lab" required>
                    <?php while ($l = mysqli_fetch_assoc($daftarLab)): ?>
                        <option value="<?= htmlspecialchars($l['id_lab']); ?>" <?= ($form['id_lab'] == $l['id_lab']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($l['nama_lab']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="field-row">
                <div class="field-group">
                    <label for="kode_barang">Kode Barang</label>
                    <input type="text" id="kode_barang" name="kode_barang"
                           value="<?= htmlspecialchars($form['kode_barang']); ?>" required>
                </div>
                <div class="field-group">
                    <label for="nama_barang">Nama Barang</label>
                    <input type="text" id="nama_barang" name="nama_barang"
                           value="<?= htmlspecialchars($form['nama_barang']); ?>" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field-group">
                    <label for="kategori">Kategori</label>
                    <input type="text" id="kategori" name="kategori"
                           value="<?= htmlspecialchars($form['kategori']); ?>">
                </div>
                <div class="field-group">
                    <label for="stok">Stok</label>
                    <input type="number" id="stok" name="stok" min="0"
                           value="<?= htmlspecialchars($form['stok']); ?>" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field-group">
                    <label for="kondisi">Kondisi</label>
                    <select id="kondisi" name="kondisi">
                        <option value="baik" <?= $form['kondisi'] === 'baik' ? 'selected' : ''; ?>>Baik</option>
                        <option value="rusak" <?= $form['kondisi'] === 'rusak' ? 'selected' : ''; ?>>Rusak</option>
                        <option value="perbaikan" <?= $form['kondisi'] === 'perbaikan' ? 'selected' : ''; ?>>Perbaikan</option>
                    </select>
                </div>
                <div class="field-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="availabel" <?= $form['status'] === 'availabel' ? 'selected' : ''; ?>>Tersedia</option>
                        <option value="tidak availabel" <?= $form['status'] === 'tidak availabel' ? 'selected' : ''; ?>>Tidak Tersedia</option>
                    </select>
                </div>
            </div>

            <div class="field-group">
                <label for="keterangan">Keterangan (opsional)</label>
                <textarea id="keterangan" name="keterangan"><?= htmlspecialchars($form['keterangan']); ?></textarea>
            </div>

            <div class="form-actions">
                <a href="hapus_data_barang.php?id=<?= urlencode($id_barang); ?>"
                   class="btn-danger-outline"
                   onclick="return confirm('Yakin ingin menghapus barang ini? Tindakan ini tidak dapat dibatalkan.')">
                    <i class="bi bi-trash"></i> Hapus
                </a>
                <div class="form-actions-right">
                    <a href="data_barang.php" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check2"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

</body>
</html>