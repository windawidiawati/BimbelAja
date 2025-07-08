<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <h3>Forum Diskusi Tutor</h3>
  <p>Diskusikan materi atau tanya jawab dengan siswa di forum.</p>
</div>

<?php include '../includes/footer.php'; ?>
