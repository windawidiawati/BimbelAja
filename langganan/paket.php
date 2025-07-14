<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Paket Langganan - BimbelAja</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
  <h2 class="text-center mb-4">Pilih Paket Langganan</h2>

  <div class="row justify-content-center">
    <!-- Paket Basic -->
    <div class="col-md-4 mb-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-primary text-white text-center">
          <h4 class="my-2">Basic</h4>
        </div>
        <div class="card-body text-center">
          <h5 class="card-title text-muted">Rp 50.000 / bulan</h5>
          <p class="card-text">Akses materi dasar untuk semua jenjang</p>

          <?php if ($isLoggedIn): ?>
            <a href="checkout.php?paket=basic" class="btn btn-outline-primary btn-block mt-3">Langganan Sekarang</a>
          <?php else: ?>
            <button class="btn btn-outline-primary btn-block mt-3" data-bs-toggle="modal" data-bs-target="#loginModal">Langganan Sekarang</button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Paket Premium -->
    <div class="col-md-4 mb-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-success text-white text-center">
          <h4 class="my-2">Premium</h4>
        </div>
        <div class="card-body text-center">
          <h5 class="card-title text-muted">Rp 100.000 / bulan</h5>
          <p class="card-text">Akses semua materi lengkap + latihan soal</p>

          <?php if ($isLoggedIn): ?>
            <a href="checkout.php?paket=premium" class="btn btn-outline-success btn-block mt-3">Langganan Sekarang</a>
          <?php else: ?>
            <button class="btn btn-outline-success btn-block mt-3" data-bs-toggle="modal" data-bs-target="#loginModal">Langganan Sekarang</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Login -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="loginModalLabel">Login Diperlukan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body text-center">
        <p>Silakan login terlebih dahulu untuk melanjutkan langganan paket belajar.</p>
      </div>
      <div class="modal-footer justify-content-center">
        <a href="/BimbelAja/auth/login.php" class="btn btn-primary">Login Sekarang</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Nanti Saja</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
