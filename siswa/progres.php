<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'siswa') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <h3>Progress Belajar</h3>
  <p>Halaman ini akan menampilkan progress belajar siswa: materi yang sudah selesai, nilai soal, dll.</p>
</div>

<?php include '../includes/footer.php'; ?>
