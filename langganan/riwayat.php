<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php");
    exit;
}

include_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user']['id'];

// Ambil semua transaksi user
$query = mysqli_query($conn, "SELECT * FROM pembayaran WHERE user_id = '$user_id' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Riwayat Transaksi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h3 class="mb-4">Riwayat Transaksi</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Paket</th>
                <th>Harga</th>
                <th>Metode</th>
                <th>Kode Unik</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($query) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['paket']) ?></td>
                        <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td><?= ucfirst($row['metode']) ?></td>
                        <td><?= $row['kode_unik'] ?: '-' ?></td>
                        <td>
                            <?php
                            $badgeClass = match($row['status']) {
                                'lunas' => 'bg-success',
                                'pending', 'menunggu_kasir' => 'bg-warning',
                                'ditolak' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= ucfirst($row['status']) ?></span>
                        </td>
                        <td><?= $row['tanggal'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">Belum ada transaksi</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
