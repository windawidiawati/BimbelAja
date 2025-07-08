<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <h3>Buat Soal</h3>
  <p>Tutor bisa menambahkan soal pilihan ganda dan kunci jawaban di sini.</p>
</div>

<?php include '../includes/footer.php'; ?>
