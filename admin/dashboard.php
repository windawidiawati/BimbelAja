<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'admin') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <h2>Dashboard Admin</h2>
  <div class="row mt-4">
    <div class="col-md-3">
      <a href="kelola_user.php" class="btn btn-primary w-100 mb-2">Kelola User</a>
      <a href="kelola_materi.php" class="btn btn-primary w-100 mb-2">Kelola Materi</a>
      <a href="verifikasi_pembayaran.php" class="btn btn-primary w-100 mb-2">Verifikasi Pembayaran</a>
      <a href="statistik.php" class="btn btn-primary w-100">Lihat Statistik</a>
    </div>
    <div class="col-md-9">
      <p>Selamat datang, <b><?= $_SESSION['user']['username']; ?></b>. Ini adalah panel kontrol admin.</p>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
