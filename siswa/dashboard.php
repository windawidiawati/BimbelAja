<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'siswa') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <h2>Dashboard Siswa</h2>
  <p>Halo, <b><?= $_SESSION['user']['username']; ?></b>. Selamat belajar!</p>
  <ul>
    <li><a href="materi.php">Lihat Materi</a></li>
    <li><a href="soal.php">Latihan Soal</a></li>
    <li><a href="kelas_online.php">Kelas Online</a></li>
    <li><a href="forum.php">Forum Diskusi</a></li>
    <li><a href="progress.php">Progress Belajar</a></li>
  </ul>
</div>

<?php include '../includes/footer.php'; ?>
