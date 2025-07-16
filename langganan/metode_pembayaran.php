<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php"); exit;
}
include_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user']['id'];
$paket_id = $_POST['paket_id'] ?? $_GET['paket_id'] ?? null;


if (!$paket_id) { echo "ID paket tidak valid."; exit; }

$query = mysqli_query($conn, "SELECT * FROM paket WHERE id = $paket_id AND status = 'aktif'");
if (!$query || mysqli_num_rows($query) === 0) {
    echo "Paket tidak ditemukan atau tidak aktif."; exit;
}

$paket = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pilih Metode Pembayaran</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h3>Metode Pembayaran untuk Paket: <?= htmlspecialchars($paket['nama']) ?></h3>
  <p><strong>Harga:</strong> Rp <?= number_format($paket['harga'], 0, ',', '.') ?></p>

  <form action="proses_pembayaran.php" method="post">
    <input type="hidden" name="paket_id" value="<?= $paket['id'] ?>">
    <input type="hidden" name="user_id" value="<?= $user_id ?>">

    <div class="mb-3">
      <label for="metode">Pilih Metode Pembayaran:</label>
      <select name="metode" class="form-select" required>
        <option value="">-- Pilih --</option>
        <option value="transfer_bank">Transfer Bank</option>
        <option value="e_wallet">E-Wallet</option>
        <option value="qris">QRIS</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button>
  </form>
</div>
</body>
</html>
