<?php
include '../config/database.php';
include '../includes/auth.php';

// Hanya kasir yang boleh akses
if ($_SESSION['user']['role'] !== 'kasir') {
  header('Location: ../index.php');
  exit;
}

// Ambil data siswa dan paket
$siswa_result = mysqli_query($conn, "SELECT * FROM users WHERE role='siswa'");
$paket_result = mysqli_query($conn, "SELECT * FROM paket WHERE status='aktif'");
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
    <a href="checkout_tunai.php" class="active"><i class="bi bi-cash-coin me-2"></i>Checkout Tunai</a>
    <a href="data_siswa.php"><i class="bi bi-people me-2"></i>Data Siswa</a>
    <a href="transaksi.php"><i class="bi bi-receipt-cutoff me-2"></i>Riwayat Transaksi</a>
</div>

<div class="content">
    <h4 class="fw-bold mb-4"><i class="bi bi-cash-coin me-2"></i>Checkout Pembayaran Tunai</h4>
    <div class="card shadow border-0">
        <div class="card-body">
            <form action="proses_checkout.php" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="user_id" class="form-label">Nama Siswa</label>
                    <select name="user_id" id="user_id" class="form-select" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php while ($siswa = mysqli_fetch_assoc($siswa_result)) : ?>
                            <option value="<?= $siswa['id'] ?>"><?= $siswa['nama'] ?> (<?= $siswa['username'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="paket_id" class="form-label">Paket Langganan</label>
                    <select name="paket_id" id="paket_id" class="form-select" required>
                        <option value="">-- Pilih Paket --</option>
                        <?php while ($paket = mysqli_fetch_assoc($paket_result)) : ?>
                            <option value="<?= $paket['id'] ?>">
                                <?= $paket['nama'] ?> - Rp<?= number_format($paket['harga']) ?> /
                                <?= $paket['durasi'] . ' ' . $paket['satuan_durasi'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
