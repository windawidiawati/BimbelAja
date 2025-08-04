<?php
include '../includes/auth.php';
include '../includes/admin_header.php';
include '../config/database.php';

// Cek role admin
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}

// Ambil data absensi
$query = "SELECT * FROM absensi_offline ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Kelola Absensi Offline</h3>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Jadwal ID</th>
                <th>Paket ID</th>
                <th>Siswa ID</th>
                <th>Status</th>
                <th>Catatan</th>
                <th>Tanggal Absensi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['jadwal_id'] ?></td>
                    <td><?= $row['paket_id'] ?></td>
                    <td><?= $row['siswa_id'] ?></td>
                    <td><?= $row['status'] ?></td>
                    <td><?= htmlspecialchars($row['catatan']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center">Tidak ada data absensi.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/admin_footer.php'; ?>
