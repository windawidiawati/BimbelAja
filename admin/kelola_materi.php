<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'admin') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <h3>Kelola Materi</h3>
  <p>Di sini admin bisa melihat & menghapus materi yang diunggah oleh tutor.</p>
</div>

<?php include '../includes/footer.php'; ?>
