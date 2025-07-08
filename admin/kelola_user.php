<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'admin') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <h3>Kelola Pengguna</h3>
  <p>Halaman ini nantinya untuk mengelola daftar siswa, tutor, dan admin.</p>
</div>

<?php include '../includes/footer.php'; ?>
