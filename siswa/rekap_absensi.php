<?php
include '../includes/auth.php';
include '../includes/admin_header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Ambil data absensi bergabung dengan data siswa dan jadwal
$sql = "SELECT a.*, 
               s.nama AS nama_siswa,
               j.tanggal,
               j.jam_mulai,
               j.jam_selesai,
               j.kategori_id,
               j.kelas_id,
               k.nama_kategori,
               kl.nama_kelas
        FROM absensi a
        JOIN siswa s ON a.siswa_id = s.id
        JOIN jadwal_kelas j ON a.jadwal_id = j.id
        JOIN kategori_materi k ON j.kategori_id = k.id
        JOIN kelas kl ON j.kelas_id = kl.id
        ORDER BY j.tanggal DESC, s.nama ASC";

$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
    <h2 class="mb-4">Rekap Absensi Siswa</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Nama Siswa</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Kategori</th>
                    <th>Kelas</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>Waktu Input</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                            <td><?= htmlspecialchars($row['tanggal']) ?></td>
                            <td><?= htmlspecialchars($row['jam_mulai'] . ' - ' . $row['jam_selesai']) ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                            <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                            <td>
                                <?php
                                    $status = $row['status'];
                                    $badge = 'secondary';
                                    if ($status == 'hadir') $badge = 'success';
                                    elseif ($status == 'izin') $badge = 'warning';
                                    elseif ($status == 'alpa') $badge = 'danger';
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= ucfirst($status) ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['catatan']) ?: '-' ?></td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center">Belum ada data absensi.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
