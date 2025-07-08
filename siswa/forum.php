<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'siswa') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <h3>Forum Diskusi</h3>
  <p>Diskusi dengan tutor atau siswa lain tentang materi atau soal.</p>
</div>

<?php include '../includes/footer.php'; ?>
