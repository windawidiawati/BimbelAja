<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php");
    exit;
}

include_once __DIR__ . '/../config/database.php';
include '../includes/header.php';

$user_id = $_SESSION['user']['id'];
$paket_id = $_POST['paket_id'] ?? $_GET['paket_id'] ?? null;

if (!$paket_id) {
    echo "ID paket tidak valid.";
    exit;
}

$query = mysqli_query($conn, "SELECT * FROM paket WHERE id = $paket_id AND status = 'aktif'");
if (!$query || mysqli_num_rows($query) === 0) {
    echo "Paket tidak ditemukan atau tidak aktif.";
    exit;
}

$paket = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pilih Metode Pembayaran</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    body {
        background: #f8f9fa;
        font-family: 'Segoe UI', sans-serif;
    }
    .card-custom {
        max-width: 600px;
        margin: auto;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        background: #fff;
    }
    .card-header {
        background: linear-gradient(90deg, #0d6efd, #6610f2);
        color: #fff;
        font-size: 1.3rem;
        font-weight: bold;
        text-align: center;
        border-radius: 16px 16px 0 0;
        padding: 15px;
    }
    .btn-primary {
        background: #0d6efd;
        border: none;
        font-weight: 600;
        transition: 0.3s;
        border-radius: 10px;
    }
    .btn-primary:hover {
        background: #0056d2;
        transform: scale(1.03);
    }
    .method-icon {
        margin-right: 8px;
    }
    .info-box {
        background: #eef3ff;
        padding: 10px 15px;
        border-radius: 10px;
        margin-bottom: 15px;
        font-size: 0.9rem;
        color: #333;
    }
</style>
</head>
<body>
<div class="container py-5">
    <div class="card card-custom">
        <div class="card-header">
            <i class="bi bi-credit-card me-2"></i>Pilih Metode Pembayaran
        </div>
        <div class="card-body p-4">
            <div class="info-box mb-3">
                <strong>Paket:</strong> <?= htmlspecialchars($paket['nama']) ?><br>
                <strong>Harga:</strong> Rp <?= number_format($paket['harga'], 0, ',', '.') ?><br>
                <strong>Durasi:</strong> <?= $paket['durasi'] . ' ' . htmlspecialchars($paket['satuan_durasi']) ?>
            </div>

            <form action="proses_pembayaran.php" method="post">
                <input type="hidden" name="paket_id" value="<?= htmlspecialchars($paket['id']) ?>">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">

                <div class="mb-3">
                    <label for="metode" class="form-label fw-semibold">Pilih Metode:</label>
                    <select name="metode" id="metode" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="transfer_bank"><i class="bi bi-bank"></i> Transfer Bank</option>
                        <option value="e_wallet"><i class="bi bi-phone"></i> E-Wallet</option>
                        <option value="qris"><i class="bi bi-qr-code"></i> QRIS</option>
                        <option value="tunai"><i class="bi bi-cash"></i> Tunai (Bayar di Kasir)</option>
                    </select>
                </div>

                <div id="tunai-info" class="alert alert-info d-none">
                    <i class="bi bi-info-circle"></i> Jika Anda memilih <strong>Tunai</strong>, sistem akan memberikan <strong>Kode Bayar</strong> yang harus diserahkan kepada kasir.
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3">
                    <i class="bi bi-check-circle me-1"></i> Konfirmasi Pembayaran
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('metode').addEventListener('change', function() {
    const tunaiInfo = document.getElementById('tunai-info');
    if (this.value === 'tunai') {
        tunaiInfo.classList.remove('d-none');
    } else {
        tunaiInfo.classList.add('d-none');
    }
});
</script>
</body>
</html>
<?php
include '../includes/footer.php';