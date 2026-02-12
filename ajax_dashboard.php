<?php
include 'koneksi.php';

$query = mysqli_query($koneksi, "
    SELECT * FROM data_pinjam 
    WHERE status = 'menunggu'
    ORDER BY tanggal DESC
");

while ($row = mysqli_fetch_assoc($query)):

$statusClass = match($row['status']) {
    'menunggu'  => 'bg-warning text-dark',
    'disetujui' => 'bg-success',
    'ditolak'   => 'bg-danger',
    default     => 'bg-secondary'
};
?>

<tr>
    <td><?= htmlspecialchars($row['nim']) ?></td>
    <td><?= htmlspecialchars($row['nama_lab']) ?></td>
    <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
    <td>
        <?= substr($row['jam_mulai'], 0, 5) ?> –
        <?= substr($row['jam_selesai'], 0, 5) ?>
    </td>
    <td>
        <span class="badge <?= $statusClass ?>">
            <?= ucfirst($row['status']) ?>
        </span>
    </td>
    <td class="text-end">
        <a href="approve_pinjam.php?id=<?= $row['id_data'] ?>"
           class="btn btn-sm btn-outline-info">
            <i class="bi bi-info-circle"></i>
        </a>
    </td>
</tr>

<?php endwhile; ?>
