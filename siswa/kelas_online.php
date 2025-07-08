<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'siswa') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <h3>Kelas Online</h3>
  <p>Jadwal dan link Zoom/Meet untuk kelas live dengan tutor.</p>
</div>

<?php include '../includes/footer.php'; ?>
