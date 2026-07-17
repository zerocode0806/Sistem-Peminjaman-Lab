<?php
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

$preselectLab = $_GET['id_lab'] ?? '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_lab      = $_POST['id_lab'] ?? '';
    $kode_barang = trim($_POST['kode_barang'] ?? '');
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori    = trim($_POST['kategori'] ?? '');
    $stok        = (int)($_POST['stok'] ?? 0);
    $kondisi     = $_POST['kondisi'] ?? 'baik';
    $status      = $_POST['status'] ?? 'availabel';
    $keterangan  = trim($_POST['keterangan'] ?? '');

    if ($id_lab === '') $errors[] = 'Lab pemilik barang wajib dipilih.';
    if ($kode_barang === '') $errors[] = 'Kode barang wajib diisi.';
    if ($nama_barang === '') $errors[] = 'Nama barang wajib diisi.';
    if ($stok < 0) $errors[] = 'Stok tidak boleh bernilai negatif.';
    if (!in_array($kondisi, ['baik','rusak','perbaikan'], true)) $errors[] = 'Kondisi tidak valid.';
    if (!in_array($status, ['availabel','tidak availabel'], true)) $errors[] = 'Status tidak valid.';

    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO data_barang (id_lab, kode_barang, nama_barang, kategori, stok, kondisi, status, keterangan)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssisss",
            $id_lab, $kode_barang, $nama_barang, $kategori, $stok, $kondisi, $status, $keterangan);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: data_barang.php?id_lab=' . urlencode($id_lab));
            exit;
        } else {
            $errors[] = 'Gagal menyimpan data: ' . mysqli_error($koneksi);
        }
    }
    $preselectLab = $id_lab;
}

$daftarLab = mysqli_query($koneksi, "SELECT id_lab, nama_lab FROM data_lab ORDER BY nama_lab ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tambah Barang – LabSystem</title>

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
        <a href="data_barang.php">Data Barang</a>
        <i class="bi bi-chevron-right"></i>
        <span class="current">Tambah Barang</span>
    </div>

    <div class="form-header">
        <h1>Tambah Barang</h1>
        <p>Menambahkan barang baru ke inventaris laboratorium</p>
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

        <form method="POST" action="tambah_data_barang.php">

            <div class="field-group">
                <label for="id_lab">Lab Pemilik</label>
                <select id="id_lab" name="id_lab" required>
                    <option value="">— Pilih Laboratorium —</option>
                    <?php while ($l = mysqli_fetch_assoc($daftarLab)): ?>
                        <option value="<?= htmlspecialchars($l['id_lab']); ?>" <?= ($preselectLab == $l['id_lab']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($l['nama_lab']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="field-row">
                <div class="field-group">
                    <label for="kode_barang">Kode Barang</label>
                    <input type="text" id="kode_barang" name="kode_barang" placeholder="Contoh: BRG-001"
                           value="<?= htmlspecialchars($_POST['kode_barang'] ?? ''); ?>" required>
                </div>
                <div class="field-group">
                    <label for="nama_barang">Nama Barang</label>
                    <input type="text" id="nama_barang" name="nama_barang" placeholder="Contoh: Router TP-Link"
                           value="<?= htmlspecialchars($_POST['nama_barang'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field-group">
                    <label for="kategori">Kategori</label>
                    <input type="text" id="kategori" name="kategori" placeholder="Contoh: Jaringan, Multimedia"
                           value="<?= htmlspecialchars($_POST['kategori'] ?? ''); ?>">
                </div>
                <div class="field-group">
                    <label for="stok">Stok</label>
                    <input type="number" id="stok" name="stok" min="0" placeholder="0"
                           value="<?= htmlspecialchars($_POST['stok'] ?? '0'); ?>" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field-group">
                    <label for="kondisi">Kondisi</label>
                    <select id="kondisi" name="kondisi">
                        <?php $selK = $_POST['kondisi'] ?? 'baik'; ?>
                        <option value="baik" <?= $selK === 'baik' ? 'selected' : ''; ?>>Baik</option>
                        <option value="rusak" <?= $selK === 'rusak' ? 'selected' : ''; ?>>Rusak</option>
                        <option value="perbaikan" <?= $selK === 'perbaikan' ? 'selected' : ''; ?>>Perbaikan</option>
                    </select>
                </div>
                <div class="field-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <?php $selS = $_POST['status'] ?? 'availabel'; ?>
                        <option value="availabel" <?= $selS === 'availabel' ? 'selected' : ''; ?>>Tersedia</option>
                        <option value="tidak availabel" <?= $selS === 'tidak availabel' ? 'selected' : ''; ?>>Tidak Tersedia</option>
                    </select>
                </div>
            </div>

            <div class="field-group">
                <label for="keterangan">Keterangan (opsional)</label>
                <textarea id="keterangan" name="keterangan" placeholder="Catatan tambahan tentang barang ini…"><?= htmlspecialchars($_POST['keterangan'] ?? ''); ?></textarea>
            </div>

            <div class="form-actions">
                <a href="data_barang.php" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check2"></i> Simpan Barang
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>