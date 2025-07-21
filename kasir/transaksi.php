<?php
include '../config/database.php';
include '../includes/auth.php';

// Pastikan hanya kasir yang bisa akses
if ($_SESSION['user']['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

// Ambil semua data pembayaran + nama siswa
$query = mysqli_query($conn, "
    SELECT p.*, u.nama 
    FROM pembayaran p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.id DESC
");

include '../includes/kasir_header.php'; // ✅ Sidebar dan style otomatis
?>

<h4 class="fw-bold mb-4"><i class="bi bi-receipt-cutoff me-2"></i>Riwayat Transaksi</h4>

<div class="card shadow border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Paket</th>
                        <th>Harga</th>
                        <th>Metode</th>
                        <th>Kode Unik</th>
                        <th>Status</th>
                        <th>Bukti Transfer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['paket']) ?></td>
                            <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
                            <td><?= ucfirst($row['metode']) ?></td>
                            <td><?= $row['kode_unik'] ?: '-' ?></td>
                            <td>
                                <?php
                                $badgeClass = match($row['status']) {
                                    'lunas' => 'bg-success',
                                    'pending', 'menunggu_kasir' => 'bg-warning text-dark',
                                    'ditolak' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($row['status']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($row['bukti_transfer'])): ?>
                                    <a href="../uploads/bukti_transfer/<?= htmlspecialchars($row['bukti_transfer']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        Lihat Bukti
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/kasir_footer.php'; ?>
