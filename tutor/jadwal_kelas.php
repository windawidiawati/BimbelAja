<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <h3>Jadwal Kelas</h3>
  <p>Atur dan lihat jadwal live class yang akan datang.</p>
</div>

<?php include '../includes/footer.php'; ?>
