<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'admin') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <h3>Verifikasi Pembayaran</h3>
  <p>Fitur untuk mengecek pembayaran siswa secara manual/otomatis.</p>
</div>

<?php include '../includes/footer.php'; ?>
