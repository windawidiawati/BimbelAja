<?php
include '../config/database.php';
include '../includes/auth.php';

// Pastikan hanya kasir yang bisa akses
if ($_SESSION['user']['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

// Ambil data siswa
$siswa_result = mysqli_query($conn, "SELECT * FROM users WHERE role='siswa'");

include '../includes/kasir_header.php'; // ✅ Sidebar otomatis
?>

<h4 class="fw-bold mb-4"><i class="bi bi-people me-2"></i>Data Siswa</h4>

<div class="card shadow border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Kelas</th>
                        <th>Jenjang</th>
                        <th>Total Transaksi</th>
                        <th>Status Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($s = mysqli_fetch_assoc($siswa_result)) {
                        $user_id = $s['id'];

                        // Hitung total transaksi
                        $total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran WHERE user_id = $user_id");
                        $total = mysqli_fetch_assoc($total_result)['total'] ?? 0;

                        // Ambil status terakhir
                        $last_result = mysqli_query($conn, "SELECT status FROM pembayaran WHERE user_id = $user_id ORDER BY tanggal DESC LIMIT 1");
                        $last_status = mysqli_fetch_assoc($last_result)['status'] ?? '-';
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($s['nama']) ?></td>
                        <td><?= htmlspecialchars($s['username']) ?></td>
                        <td><?= htmlspecialchars($s['kelas']) ?></td>
                        <td><?= htmlspecialchars($s['jenjang']) ?></td>
                        <td><?= $total ?></td>
                        <td>
                            <?php if ($last_status === 'berhasil' || $last_status === 'lunas'): ?>
                                <span class="badge bg-success">Lunas</span>
                            <?php elseif ($last_status === 'pending' || $last_status === 'menunggu_kasir'): ?>
                                <span class="badge bg-warning text-dark">Menunggu</span>
                            <?php elseif ($last_status === 'gagal'): ?>
                                <span class="badge bg-danger">Gagal</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/kasir_footer.php'; ?>
