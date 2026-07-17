<?php
include 'koneksi.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$type  = $data['type']  ?? '';
$id    = $data['id']    ?? '';
$field = $data['field'] ?? '';
$value = $data['value'] ?? '';

// Whitelist kolom yang boleh diubah agar aman dari SQL injection lewat nama kolom
$allowedFields = [
    'ac'   => ['kondisi'],
    'meja' => ['cpu_kondisi', 'keyboard_kondisi', 'mouse_kondisi', 'monitor_kondisi', 'kursi_kondisi'],
    'lab'  => ['jumlah_kursi'],
];

$allowedValues = [
    'kondisi'          => ['normal', 'rusak'],
    'cpu_kondisi'      => ['normal', 'rusak', 'instal_ulang'],
    'keyboard_kondisi' => ['normal', 'rusak', 'tidak_ada'],
    'mouse_kondisi'    => ['normal', 'rusak', 'tidak_ada'],
    'monitor_kondisi'  => ['normal', 'rusak', 'tidak_ada'],
    'kursi_kondisi'    => ['normal', 'rusak', 'tidak_ada'],
];

if (!isset($allowedFields[$type]) || !in_array($field, $allowedFields[$type], true)) {
    echo json_encode(['success' => false, 'message' => 'Field tidak valid']);
    exit;
}

$id = mysqli_real_escape_string($koneksi, $id);

if ($type === 'lab') {
    $value = (int) $value;
    $sql = "UPDATE data_lab SET jumlah_kursi = '$value' WHERE id_lab = '$id'";
} else {
    if (!isset($allowedValues[$field]) || !in_array($value, $allowedValues[$field], true)) {
        echo json_encode(['success' => false, 'message' => 'Nilai tidak valid']);
        exit;
    }
    $value = mysqli_real_escape_string($koneksi, $value);
    $table = $type === 'ac' ? 'inventaris_ac' : 'inventaris_meja';
    $pk    = $type === 'ac' ? 'id_ac' : 'id_meja';
    $sql   = "UPDATE $table SET $field = '$value' WHERE $pk = '$id'";
}

if (mysqli_query($koneksi, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($koneksi)]);
}