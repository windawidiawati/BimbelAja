<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user']);

include_once __DIR__ . '/../config/database.php';

// Ambil hanya paket yang status-nya aktif
$query = "SELECT * FROM paket WHERE status = 'aktif'";
$result = mysqli_query($conn, $query);
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
    <?php while ($paket = mysqli_fetch_assoc($result)): ?>
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header text-white text-center" style="background-color: <?= htmlspecialchars($paket['warna'] ?: '#0d6efd') ?>;">
            <h4 class="my-2"><?= htmlspecialchars($paket['nama']) ?></h4>
          </div>
          <div class="card-body text-center">
            <h5 class="card-title text-muted">Rp <?= number_format($paket['harga'], 0, ',', '.') ?> / <?= $paket['durasi'] ?> hari</h5>
            <p><strong>Jenjang:</strong> <?= htmlspecialchars($paket['jenjang']) ?></p>
            <p><strong>Kelas:</strong> <?= htmlspecialchars($paket['kelas']) ?></p>
            <p class="card-text"><?= htmlspecialchars($paket['deskripsi']) ?></p>

            <?php if ($isLoggedIn): ?>
              <a href="/BimbelAja/langganan/checkout.php?paket_id=<?= $paket['id'] ?>" class="btn btn-outline-primary btn-block mt-3">Langganan Sekarang</a>
            <?php else: ?>
              <button class="btn btn-outline-primary btn-block mt-3" data-bs-toggle="modal" data-bs-target="#loginModal">Langganan Sekarang</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
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
