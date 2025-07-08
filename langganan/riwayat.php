<?php
include '../includes/auth.php';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $paket = $_POST['paket'];
  $harga = $_POST['harga'];
  $tanggal = date('Y-m-d H:i:s');

  // Simulasi penyimpanan data
  $message = "Langganan $paket berhasil dibayar sebesar Rp " . number_format($harga, 0, ',', '.') . " pada $tanggal.";
}
?>

<div class="container mt-5">
  <h2>Riwayat Langganan</h2>
  <?php if (isset($message)): ?>
    <div class="alert alert-success"><?= $message; ?></div>
  <?php else: ?>
    <p>Belum ada transaksi. Silakan pilih paket langganan.</p>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
