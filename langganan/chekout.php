<?php
include '../includes/auth.php';
include '../includes/header.php';

$paket = $_GET['paket'] ?? 'basic';
$harga = ($paket === 'premium') ? 100000 : 50000;
?>

<div class="container mt-5">
  <h2>Checkout</h2>
  <p>Paket: <b><?= ucfirst($paket); ?></b></p>
  <p>Harga: <b>Rp <?= number_format($harga, 0, ',', '.'); ?></b></p>

  <form method="POST" action="riwayat.php">
    <input type="hidden" name="paket" value="<?= $paket; ?>">
    <input type="hidden" name="harga" value="<?= $harga; ?>">
    <button type="submit" class="btn btn-success">Bayar Sekarang</button>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
