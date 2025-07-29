<?php
include '../config/database.php';
include '../includes/auth.php';

if ($_SESSION['user']['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

$query = mysqli_query($conn, "
    SELECT p.*, u.nama 
    FROM pembayaran p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.id DESC
");

$hari = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];

include '../includes/kasir_header.php';
?>

<div class="container-fluid">
    <h4 class="fw-bold mb-4"><i class="bi bi-receipt-cutoff me-2"></i>Riwayat Transaksi</h4>

    <div class="card shadow border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-nowrap">No</th>
                            <th class="text-nowrap">Nama Siswa</th>
                            <th class="text-nowrap">Paket</th>
                            <th class="text-nowrap">Harga</th>
                            <th class="text-nowrap">Metode</th>
                            <th class="text-nowrap">Status</th>
                            <th class="text-nowrap">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($query)): ?>
                            <?php
                            $tanggal_format = '-';
                            if (!empty($row['tanggal'])) {
                                $day = $hari[date('l', strtotime($row['tanggal']))];
                                $tanggal_format = $day . ', ' . date('d-m-Y', strtotime($row['tanggal']));
                            }
                            ?>
                            <tr>
                                <td class="text-nowrap"><?= $no++ ?></td>
                                <td class="text-nowrap"><?= htmlspecialchars($row['nama']) ?></td>
                                <td class="text-nowrap"><?= htmlspecialchars($row['paket']) ?></td>
                                <td class="text-nowrap">Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td class="text-nowrap small text-capitalize"><?= $row['metode'] ?></td>
                                <td class="text-nowrap">
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
                                <td class="text-nowrap"><?= $tanggal_format ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/kasir_footer.php'; ?>
