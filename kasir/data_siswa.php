<?php
include '../config/database.php';
include '../includes/auth.php';

if ($_SESSION['user']['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

$siswa_result = mysqli_query($conn, "SELECT * FROM users WHERE role='siswa'");
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
    <a href="data_siswa.php" class="active"><i class="bi bi-people me-2"></i>Data Siswa</a>
    <a href="transaksi.php"><i class="bi bi-receipt-cutoff me-2"></i>Riwayat Transaksi</a>
</div>

<div class="content">
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
                                <?php if ($last_status === 'berhasil'): ?>
                                    <span class="badge bg-success">Berhasil</span>
                                <?php elseif ($last_status === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
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
</div>

<?php include '../includes/footer.php'; ?>
