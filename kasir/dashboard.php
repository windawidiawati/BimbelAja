<?php
include '../includes/kasir_header.php';
include '../config/database.php';

// Ambil total transaksi
$totalTransaksi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran"))['total'];

// Ambil transaksi pending
$totalPending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran WHERE status = 'pending'"))['total'];

// Ambil jumlah siswa terdaftar
$totalSiswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'siswa'"))['total'];

// Ambil 5 transaksi terbaru
$transaksiBaru = mysqli_query($conn, "
    SELECT p.*, u.nama 
    FROM pembayaran p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.tanggal DESC
    LIMIT 5
");
?>

<h4 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2"></i>Dashboard Kasir</h4>

<div class="row g-4">
    <!-- Total Transaksi -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-receipt-cutoff display-5 text-primary mb-3"></i>
                <h5 class="fw-bold">Total Transaksi</h5>
                <p class="fs-4 text-dark"><?= $totalTransaksi ?></p>
            </div>
        </div>
    </div>

    <!-- Transaksi Pending -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-hourglass-split display-5 text-warning mb-3"></i>
                <h5 class="fw-bold">Menunggu Verifikasi</h5>
                <p class="fs-4 text-dark"><?= $totalPending ?></p>
            </div>
        </div>
    </div>

    <!-- Siswa Terdaftar -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-people display-5 text-success mb-3"></i>
                <h5 class="fw-bold">Siswa Terdaftar</h5>
                <p class="fs-4 text-dark"><?= $totalSiswa ?></p>
            </div>
        </div>
    </div>
</div>

<hr class="my-4">

<!-- Tabel Transaksi Terbaru -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Transaksi Terbaru</h6>
    </div>
    <div class="card-body">
        <table class="table table-striped text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Siswa</th>
                    <th>Paket</th>
                    <th>Harga</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($row = mysqli_fetch_assoc($transaksiBaru)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['paket']) ?></td>
                        <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td>
                            <?php
                            $badge = match($row['status']) {
                                'lunas' => 'success',
                                'pending' => 'warning text-dark',
                                'ditolak' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $badge ?>"><?= ucfirst($row['status']) ?></span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include '../includes/kasir_footer.php';
?>
