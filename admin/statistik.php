<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'admin') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <h3>Statistik</h3>
  <p>Grafik jumlah pengguna, materi, kelas aktif, dan lain-lain (akan ditambahkan).</p>
</div>

<?php include '../includes/footer.php'; ?>
