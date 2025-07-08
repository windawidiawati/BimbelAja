<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'siswa') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <h3>Latihan Soal</h3>
  <p>Kerjakan soal pilihan ganda dari tutor dan lihat hasilnya.</p>
</div>

<?php include '../includes/footer.php'; ?>
