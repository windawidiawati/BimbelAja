<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'siswa') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <div class="text-center mb-4">
    <h2 class="fw-bold">Dashboard Siswa</h2>
    <p>Halo, <strong><?= htmlspecialchars($_SESSION['user']['username']); ?></strong>. Selamat belajar dan semangat terus!</p>
  </div>

  <div class="row row-cols-1 row-cols-md-3 g-4">
    <!-- Materi -->
    <div class="col">
      <a href="materi.php" class="text-decoration-none text-dark">
        <div class="card shadow-sm h-100 text-center">
          <div class="card-body">
            <i class="bi bi-journal-text fs-1 text-primary"></i>
            <h5 class="card-title mt-2">Materi</h5>
            <p class="card-text">Lihat materi lengkap dari tutor profesional.</p>
          </div>
        </div>
      </a>
    </div>

    <!-- Soal -->
    <div class="col">
      <a href="soal.php" class="text-decoration-none text-dark">
        <div class="card shadow-sm h-100 text-center">
          <div class="card-body">
            <i class="bi bi-pencil-square fs-1 text-success"></i>
            <h5 class="card-title mt-2">Latihan Soal</h5>
            <p class="card-text">Uji kemampuanmu dengan latihan soal interaktif.</p>
          </div>
        </div>
      </a>
    </div>

    <!-- Kelas Online -->
    <div class="col">
      <a href="kelas_online.php" class="text-decoration-none text-dark">
        <div class="card shadow-sm h-100 text-center">
          <div class="card-body">
            <i class="bi bi-camera-video-fill fs-1 text-danger"></i>
            <h5 class="card-title mt-2">Kelas Online</h5>
            <p class="card-text">Ikuti kelas online langsung bersama tutor.</p>
          </div>
        </div>
      </a>
    </div>

    <!-- Forum -->
    <div class="col">
      <a href="forum.php" class="text-decoration-none text-dark">
        <div class="card shadow-sm h-100 text-center">
          <div class="card-body">
            <i class="bi bi-chat-dots-fill fs-1 text-warning"></i>
            <h5 class="card-title mt-2">Forum Diskusi</h5>
            <p class="card-text">Diskusi bersama teman dan tutor.</p>
          </div>
        </div>
      </a>
    </div>

    <!-- Progress -->
    <div class="col">
      <a href="progress.php" class="text-decoration-none text-dark">
        <div class="card shadow-sm h-100 text-center">
          <div class="card-body">
            <i class="bi bi-bar-chart-line-fill fs-1 text-info"></i>
            <h5 class="card-title mt-2">Progress Belajar</h5>
            <p class="card-text">Lihat perkembangan belajarmu secara berkala.</p>
          </div>
        </div>
      </a>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
