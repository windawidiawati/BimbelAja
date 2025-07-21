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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_unik = trim($_POST['kode_unik'] ?? '');

    if (empty($kode_unik)) {
        $error = "Kode unik wajib diisi!";
    } else {
        // Cari transaksi (case-insensitive)
        $stmt = $conn->prepare("SELECT * FROM pembayaran WHERE LOWER(kode_unik) = LOWER(?) AND status = 'menunggu_kasir'");
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
                $error = "Gagal memperbarui status pembayaran.";
            }
        } else {
            $error = "Kode unik tidak ditemukan atau sudah diverifikasi.";
        }
    }
}

include '../includes/kasir_header.php'; // ✅ Sidebar & navbar otomatis
?>

<div class="container-fluid">
    <h4 class="fw-bold mb-4"><i class="bi bi-cash-coin me-2"></i>Verifikasi Pembayaran Tunai</h4>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="card mb-4" style="max-width: 500px;"> <!-- ✅ Posisi rapat kiri -->
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="kode_unik" class="form-label">Masukkan Kode Unik</label>
                    <input type="text" name="kode_unik" id="kode_unik" class="form-control" placeholder="Contoh: TUNAI123456" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle me-1"></i>Verifikasi
                </button>
            </form>
        </div>
    </div>

    <?php if ($transaksi): ?>
        <div class="card" style="max-width: 500px;"> <!-- ✅ Ikut rapat kiri -->
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

<?php include '../includes/kasir_footer.php'; ?>
