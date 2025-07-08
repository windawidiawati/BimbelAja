<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <h2>Dashboard Tutor</h2>
  <p>Halo, <b><?= $_SESSION['user']['username']; ?></b>. Selamat mengajar!</p>
  <ul>
    <li><a href="unggah_materi.php">Unggah Materi</a></li>
    <li><a href="buat_soal.php">Buat Soal</a></li>
    <li><a href="jadwal_kelas.php">Jadwal Kelas</a></li>
    <li><a href="forum.php">Forum Diskusi</a></li>
  </ul>
</div>

<?php include '../includes/footer.php'; ?>
