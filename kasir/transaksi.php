<?php
include '../config/database.php';
include '../includes/auth.php';

if ($_SESSION['user']['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

// Verifikasi & tolak transaksi
if (isset($_GET['verifikasi'])) {
    $id = intval($_GET['verifikasi']);
    mysqli_query($conn, "UPDATE pembayaran SET status='lunas' WHERE id=$id");
}
if (isset($_GET['tolak'])) {
    $id = intval($_GET['tolak']);
    mysqli_query($conn, "UPDATE pembayaran SET status='ditolak' WHERE id=$id");
}

// Ambil data transaksi
$result = mysqli_query($conn, "
    SELECT p.*, u.nama AS nama_siswa 
    FROM pembayaran p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.tanggal DESC
");
?>

<?php include '../includes/header.php'; ?>

<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f8f9fa;
    }
    .sidebar {
        width: 240px;
        background-color: #0d6efd;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        padding-top: 20px;
    }
    .sidebar a {
        color: white;
        display: block;
        padding: 12px 20px;
        text-decoration: none;
        font-size: 16px;
    }
    .sidebar a:hover {
        background-color: #0b5ed7;
    }
    .sidebar .logo {
        text-align: center;
        margin-bottom: 30px;
        color: white;
        font-size: 20px;
        font-weight: bold;
    }
    .content {
        margin-left: 240px;
        padding: 20px;
    }
</style>

<div class="sidebar">
    <div class="logo">
        <i class="bi bi-cash-stack me-2"></i>BimbelAja
    </div>
    <a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="checkout_tunai.php"><i class="bi bi-cash-coin me-2"></i>Checkout Tunai</a>
    <a href="data_siswa.php"><i class="bi bi-people me-2"></i>Data Siswa</a>
    <a href="transaksi.php" class="active"><i class="bi bi-receipt-cutoff me-2"></i>Riwayat Transaksi</a>
</div>

<div class="content">
    <h4 class="fw-bold mb-4"><i class="bi bi-receipt-cutoff me-2"></i>Riwayat Transaksi</h4>

    <?php if (isset($_GET['berhasil'])): ?>
        <div class="alert alert-success">Transaksi berhasil ditambahkan.</div>
    <?php endif; ?>

    <div class="card shadow border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Paket</th>
                            <th>Harga</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Bukti</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                                <td><?= htmlspecialchars($row['paket'] ?? '-') ?></td>
                                <td>Rp<?= number_format($row['harga'] ?? 0) ?></td>
                                <td>
                                    <?php 
                                        $metode = $row['metode'] ?? '-';
                                        echo '<span class="badge bg-info text-dark">' . htmlspecialchars(ucfirst($metode)) . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        $status = $row['status'] ?? '-';
                                        $badgeClass = match($status) {
                                            'lunas' => 'success',
                                            'pending' => 'warning text-dark',
                                            'ditolak' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($row['bukti_transfer'])): ?>
                                        <a href="../uploads/<?= htmlspecialchars($row['bukti_transfer']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">Lihat</a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                <td>
                                    <?php if (($row['status'] ?? '') === 'pending'): ?>
                                        <a href="?verifikasi=<?= $row['id'] ?>" class="btn btn-sm btn-success me-1">✔</a>
                                        <a href="?tolak=<?= $row['id'] ?>" class="btn btn-sm btn-danger">✖</a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
