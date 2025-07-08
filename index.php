<?php include 'includes/header.php'; ?>

<div class="container py-5">
  <div class="row align-items-center">
    <div class="col-md-6">
      <h1 class="fw-bold mb-3">Belajar Lebih Mudah <br>dan Menyenangkan <br>Bersama BimbelAja!</h1>
      <a href="#" class="btn btn-warning btn-lg text-white">Mulai Sekarang</a>
    </div>
    <div class="col-md-6 text-center">
      <img src="assets/images/hero.png" alt="Belajar Online" class="img-fluid" style="max-height: 300px;">
    </div>
  </div>
</div>

<!-- Fitur Unggulan -->
<div class="bg-light py-5">
  <div class="container text-center">
    <div class="row">
      <div class="col-md-4">
        <img src="assets/images/icon1.png" alt="Materi" class="mb-3" style="height:50px;">
        <h5 class="fw-bold">Akses Materi Lengkap</h5>
        <p>Video + PDF untuk semua mata pelajaran</p>
      </div>
      <div class="col-md-4">
        <img src="assets/images/icon2.png" alt="Soal" class="mb-3" style="height:50px;">
        <h5 class="fw-bold">Latihan Soal Interaktif</h5>
        <p>Dilengkapi pembahasan</p>
      </div>
      <div class="col-md-4">
        <img src="assets/images/icon3.png" alt="Kelas" class="mb-3" style="height:50px;">
        <h5 class="fw-bold">Kelas Live & Diskusi</h5>
        <p>Belajar langsung dengan tutor berpengalaman</p>
      </div>
    </div>
  </div>
</div>

<!-- Testimoni -->
<div class="container py-5 text-center">
  <img src="assets/images/user.png" alt="User" style="height:60px;" class="mb-2">
  <blockquote class="blockquote">
    <p class="mb-0">Belajarnya jadi lebih fokus & fleksibel.</p>
    <footer class="blockquote-footer">Ahmad</footer>
  </blockquote>
</div>

<!-- Paket -->
<div class="container pb-5">
  <div class="row text-center">
    <div class="col-md-4">
      <div class="border rounded p-4">
        <h5>Gratis</h5>
        <a href="#" class="btn btn-primary mt-2">Daftar Sekarang</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="border rounded p-4">
        <h5>Premium Bulanan</h5>
        <a href="#" class="btn btn-primary mt-2">Daftar Sekarang</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="border rounded p-4">
        <h5>Premium Tahunan</h5>
        <a href="#" class="btn btn-primary mt-2">Daftar Sekarang</a>
      </div>
    </div>
  </div>
</div>

<!-- Footer -->
<div class="bg-light py-4">
  <div class="container text-center">
    <p><a href="#">Tentang Kami</a> | <a href="#">Syarat & Ketentuan</a> | <a href="#">Kontak</a></p>
    <p class="text-muted">&copy; <?= date('Y'); ?> BimbelAja</p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
