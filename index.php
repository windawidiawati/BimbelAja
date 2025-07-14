<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<div class="container py-5">
  <div class="row align-items-center">
    <div class="col-md-6 mb-4 mb-md-0">
      <h1 class="fw-bold mb-3 display-5 text-primary">
        Belajar Lebih Mudah <br>dan Menyenangkan <br>Bersama <span class="text-warning">BimbelAja!</span>
      </h1>
      <p class="mb-4 text-muted">Temukan cara belajar paling efektif dan menyenangkan dengan bimbingan berkualitas.</p>
      <a href="#paket" class="btn btn-warning btn-lg text-white shadow-sm">Mulai Sekarang</a>
    </div>
    <div class="col-md-6 text-center">
      <img src="assets/images/hero.png" alt="Belajar Online" class="img-fluid" style="max-height: 350px;">
    </div>
  </div>
</div>

<!-- Fitur Unggulan -->
<div class="bg-light py-5">
  <div class="container">
    <h2 class="text-center fw-bold mb-5">Kenapa Pilih BimbelAja?</h2>
    <div class="row g-4 text-center">
      <div class="col-md-4">
        <div class="p-4 border rounded bg-white shadow-sm h-100">
          <img src="assets/images/icon1.png" alt="Materi" class="mb-3" style="height:50px;">
          <h5 class="fw-bold">Akses Materi Lengkap</h5>
          <p class="text-muted">Video dan PDF untuk semua mata pelajaran yang kamu butuhkan.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 border rounded bg-white shadow-sm h-100">
          <img src="assets/images/icon2.png" alt="Soal" class="mb-3" style="height:50px;">
          <h5 class="fw-bold">Latihan Soal Interaktif</h5>
          <p class="text-muted">Lengkap dengan pembahasan dan skor latihan secara otomatis.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 border rounded bg-white shadow-sm h-100">
          <img src="assets/images/icon3.png" alt="Kelas" class="mb-3" style="height:50px;">
          <h5 class="fw-bold">Kelas Live & Diskusi</h5>
          <p class="text-muted">Belajar langsung dengan tutor berpengalaman dan forum diskusi aktif.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Testimoni -->
<div class="container py-5 text-center">
  <div class="mx-auto" style="max-width: 600px;">
    <img src="assets/images/user.png" alt="User" class="rounded-circle mb-3" style="height:70px;">
    <blockquote class="blockquote">
      <p class="fs-5 fst-italic">“Belajarnya jadi lebih fokus & fleksibel. Saya bisa atur waktu sendiri.”</p>
      <footer class="blockquote-footer">Ahmad, Siswa Kelas 12</footer>
    </blockquote>
  </div>
</div>

<!-- Paket -->
<div id="paket" class="bg-light py-5">
  <div class="container">
    <h2 class="text-center fw-bold mb-5">Pilih Paket Belajar Kamu</h2>
    <?php include 'langganan/paket.php'; ?>
  </div>
</div>

<!-- Footer -->
<footer class="bg-white border-top py-4 mt-5">
  <div class="container text-center">
    <p class="mb-2">
      <a href="#" class="text-decoration-none me-3">Tentang Kami</a>
      <a href="#" class="text-decoration-none me-3">Syarat & Ketentuan</a>
      <a href="#" class="text-decoration-none">Kontak</a>
    </p>
    <p class="text-muted small mb-0">&copy; <?= date('Y'); ?> BimbelAja. All rights reserved.</p>
  </div>
</footer>

<?php include 'includes/footer.php'; ?>
