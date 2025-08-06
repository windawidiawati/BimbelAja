<?php
include '../includes/auth.php';
include '../includes/kasir_header.php';
include '../config/database.php';

// Pastikan hanya kasir yang bisa akses
if ($_SESSION['user']['role'] !== 'kasir') {
    header('Location: ../index.php');
    exit;
}

// Ambil data siswa yang sudah berlangganan
$query = "
    SELECT u.id, u.nama, u.email, u.kelas
    FROM users u
    INNER JOIN langganan l ON u.id = l.user_id
    WHERE u.role = 'siswa'
";

$result = mysqli_query($conn, $query);
?>

<div class="container-fluid">
    <h4 class="fw-bold mb-4"><i class="bi bi-person-check-fill me-2"></i>Data Siswa Berlangganan</h4>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-primary">
                <tr>
                    <th>No</th>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Kelas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0) : ?>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['kelas']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4" class="text-center">Belum ada siswa berlangganan</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/kasir_footer.php'; ?>
