<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php");
    exit;
}

include_once __DIR__ . '/../config/database.php';

$paket_id = $_GET['paket_id'] ?? null;
if (!$paket_id || !is_numeric($paket_id)) {
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
<title>Checkout Paket | BimbelAja</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    body {
        background: #f8f9fa;
        font-family: 'Segoe UI', sans-serif;
    }
    .card-custom {
        max-width: 700px;
        margin: auto;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
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
    .btn-success {
        border-radius: 10px;
        font-weight: 600;
        transition: 0.3s;
    }
    .btn-success:hover {
        background: #198754;
        transform: scale(1.03);
    }
    .info-box {
        background: #eef3ff;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
</style>
</head>
<body>
<div class="container py-5">
    <div class="card card-custom">
        <div class="card-header">
            <i class="bi bi-cart-check me-2"></i> Checkout Paket
        </div>
        <div class="card-body p-4">
            <div class="info-box">
                <h5 class="fw-bold mb-3"><?= htmlspecialchars($paket['nama']) ?></h5>
                <p><strong>Kategori:</strong> <?= htmlspecialchars($paket['kategori']) ?></p>
                <p><strong>Jenjang:</strong> <?= htmlspecialchars($paket['jenjang']) ?> - Kelas <?= htmlspecialchars($paket['kelas']) ?></p>
                <p><strong>Harga:</strong> Rp <?= number_format($paket['harga'], 0, ',', '.') ?></p>
                <p><strong>Durasi:</strong> <?= $paket['durasi'] . ' ' . htmlspecialchars($paket['satuan_durasi']) ?></p>
                <p><strong>Deskripsi:</strong><br><?= nl2br(htmlspecialchars($paket['deskripsi'])) ?></p>
            </div>

            <form action="metode_pembayaran.php" method="post">
                <input type="hidden" name="paket_id" value="<?= htmlspecialchars($paket['id']) ?>">
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-credit-card me-1"></i> Lanjut Pilih Metode Pembayaran
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
