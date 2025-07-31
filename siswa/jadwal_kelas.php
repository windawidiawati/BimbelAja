<?php
include '../includes/auth.php';
include '../includes/admin_header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php"); exit;
}

$query = "
    SELECT 
        jk.id,
        t.nama AS nama_tutor,
        k.nama_kategori,
        kl.nama_kelas,
        jk.tanggal,
        jk.jam_mulai,
        jk.jam_selesai,
        jk.keterangan,
        jk.materi_file,
        jk.created_at
    FROM jadwal_kelas jk
    LEFT JOIN tutor t ON jk.tutor_id = t.id
    LEFT JOIN kategori_materi k ON jk.kategori_id = k.id
    LEFT JOIN kelas kl ON jk.kelas_id = kl.id
    ORDER BY jk.tanggal DESC, jk.jam_mulai ASC
";

$result = mysqli_query($conn, $query);
?>

<div class="container mt-4">
    <h2 class="mb-4">Jadwal Kelas</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Tutor</th>
                <th>Kategori</th>
                <th>Kelas</th>
                <th>Tanggal</th>
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
                <th>Keterangan</th>
                <th>Materi File</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama_tutor']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                        <td><?= htmlspecialchars($row['tanggal']) ?></td>
                        <td><?= htmlspecialchars($row['jam_mulai']) ?></td>
                        <td><?= htmlspecialchars($row['jam_selesai']) ?></td>
                        <td><?= htmlspecialchars($row['keterangan']) ?></td>
                        <td>
                            <?php if (!empty($row['materi_file'])): ?>
                                <a href="../uploads/materi/<?= $row['materi_file'] ?>" target="_blank">Lihat</a>
                            <?php else: ?>
                                <em>-</em>
                            <?php endif; ?>
                        </td>
                        <td><?= $row['created_at'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="10" class="text-center">Belum ada jadwal</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/admin_footer.php'; ?>
