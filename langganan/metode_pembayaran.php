<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php");
    exit;
}

include_once __DIR__ . '/../config/database.php';

$paket_id = $_POST['paket_id'] ?? null;
$user_id = $_POST['user_id'] ?? null;

if (!$paket_id || !$user_id) {
    echo "Data tidak lengkap.";
    exit;
}

$query = mysqli_query($conn, "SELECT * FROM paket WHERE id = $paket_id AND status = 'aktif'");
if (!$query || mysqli_num_rows($query) === 0) {
    echo "Paket tidak ditemukan.";
    exit;
}

$paket = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pilih Metode Pembayaran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 shadow-sm">
        <h3 class="mb-3">Pilih Metode Pembayaran</h3>
        <p><strong>Paket:</strong> <?= htmlspecialchars($paket['nama']) ?> - Rp<?= number_format($paket['harga'], 0, ',', '.') ?></p>
        
        <form action="proses_pembayaran.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
            <input type="hidden" name="paket_id" value="<?= htmlspecialchars($paket_id) ?>">

            <div class="mb-3">
                <label class="form-label">Metode</label>
                <select name="metode" id="metode" class="form-select" onchange="toggleBuktiTransfer()" required>
                    <option value="">-- Pilih Metode --</option>
                    <option value="tunai">Tunai (Bayar di Kasir)</option>
                    <option value="transfer">Transfer Bank</option>
                </select>
            </div>

            <div class="mb-3" id="bukti-transfer-section" style="display:none;">
                <label class="form-label">Upload Bukti Transfer</label>
                <input type="file" name="bukti_transfer" class="form-control" accept="image/*">
                <small class="text-muted">Hanya untuk metode transfer.</small>
            </div>

            <button type="submit" class="btn btn-primary w-100">Lanjutkan</button>
        </form>
    </div>
</div>

<script>
    function toggleBuktiTransfer() {
        const metode = document.getElementById('metode').value;
        document.getElementById('bukti-transfer-section').style.display = (metode === 'transfer') ? 'block' : 'none';
    }
</script>
</body>
</html>
