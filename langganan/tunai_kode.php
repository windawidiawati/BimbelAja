<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php"); exit;
}

$kode = $_GET['kode'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kode Pembayaran Tunai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 text-center">
    <h3>Pembayaran Tunai</h3>
    <p>Tunjukkan kode berikut ke kasir untuk menyelesaikan pembayaran:</p>
    <div class="alert alert-primary fs-4 fw-bold"><?= htmlspecialchars($kode) ?></div>
    <a href="riwayat.php" class="btn btn-success">Lihat Riwayat Pembayaran</a>
</div>
</body>
</html>
