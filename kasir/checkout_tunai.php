<?php
include '../config/database.php';
include '../includes/auth.php';

// Hanya kasir yang boleh akses
if ($_SESSION['user']['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

$success = $error = '';
$transaksi = null;

// Proses verifikasi kode unik
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_unik = trim($_POST['kode_unik'] ?? '');

    if (empty($kode_unik)) {
        $error = "Kode unik wajib diisi!";
    } else {
        // Cari transaksi dengan status menunggu_kasir
        $stmt = $conn->prepare("SELECT * FROM pembayaran WHERE kode_unik = ? AND status = 'menunggu_kasir'");
        $stmt->bind_param("s", $kode_unik);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $transaksi = $result->fetch_assoc();

            // Update status menjadi lunas
            $update = $conn->prepare("UPDATE pembayaran SET status='lunas' WHERE id = ?");
            $update->bind_param("i", $transaksi['id']);
            if ($update->execute()) {
                $success = "Pembayaran berhasil diverifikasi!";
            } else {
                $error = "Gagal memperbarui status.";
            }
        } else {
            $error = "Kode unik tidak ditemukan atau sudah diverifikasi.";
        }
    }
}
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
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .btn-primary {
        border-radius: 8px;
        font-weight: bold;
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
    <h4 class="fw-bold mb-4"><i class="bi bi-cash-coin me-2"></i>Verifikasi Pembayaran Tunai</h4>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Form Input Kode Unik -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="kode_unik" class="form-label">Masukkan Kode Unik</label>
                    <input type="text" name="kode_unik" id="kode_unik" class="form-control" placeholder="Contoh: AB12345" required>
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-circle me-1"></i>Verifikasi</button>
            </form>
        </div>
    </div>

    <!-- Jika transaksi ditemukan -->
    <?php if ($transaksi): ?>
        <div class="card">
            <div class="card-header bg-success text-white">
                <strong>Detail Transaksi</strong>
            </div>
            <div class="card-body">
                <p><strong>Nama Paket:</strong> <?= htmlspecialchars($transaksi['paket']) ?></p>
                <p><strong>Harga:</strong> Rp<?= number_format($transaksi['harga'], 0, ',', '.') ?></p>
                <p><strong>Kode Unik:</strong> <?= htmlspecialchars($transaksi['kode_unik']) ?></p>
                <p><strong>Status:</strong> <span class="badge bg-success">Lunas</span></p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
