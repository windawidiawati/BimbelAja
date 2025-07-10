<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
?>

<div class="container mt-5">
  <div class="text-center mb-5">
    <h2 class="fw-bold">Dashboard Tutor</h2>
    <p>Selamat datang, <b><?= $_SESSION['user']['username']; ?></b>. Semangat mengajar hari ini!</p>
  </div>

  <div class="row g-4">
    <div class="col-md-6 col-lg-3">
      <a href="unggah_materi.php" class="text-decoration-none">
        <div class="card text-center shadow-sm h-100 hover-shadow">
          <div class="card-body">
            <div class="mb-2"><i class="bi bi-upload text-primary" style="font-size: 2rem;"></i></div>
            <h5 class="card-title">Unggah Materi</h5>
            <p class="card-text small text-muted">Tambahkan materi berupa video atau PDF</p>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-6 col-lg-3">
      <a href="buat_soal.php" class="text-decoration-none">
        <div class="card text-center shadow-sm h-100">
          <div class="card-body">
            <div class="mb-2"><i class="bi bi-pencil-square text-success" style="font-size: 2rem;"></i></div>
            <h5 class="card-title">Buat Soal</h5>
            <p class="card-text small text-muted">Susun soal latihan atau ujian</p>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-6 col-lg-3">
      <a href="jadwal_kelas.php" class="text-decoration-none">
        <div class="card text-center shadow-sm h-100">
          <div class="card-body">
            <div class="mb-2"><i class="bi bi-calendar-event text-warning" style="font-size: 2rem;"></i></div>
            <h5 class="card-title">Jadwal Kelas</h5>
            <p class="card-text small text-muted">Lihat dan atur jadwal kelas</p>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-6 col-lg-3">
      <a href="forum.php" class="text-decoration-none">
        <div class="card text-center shadow-sm h-100">
          <div class="card-body">
            <div class="mb-2"><i class="bi bi-chat-dots text-danger" style="font-size: 2rem;"></i></div>
            <h5 class="card-title">Forum Diskusi</h5>
            <p class="card-text small text-muted">Diskusi dengan siswa dan tutor lain</p>
          </div>
        </div>
      </a>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
