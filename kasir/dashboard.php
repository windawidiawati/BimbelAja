<?php
include '../includes/auth.php';
include '../includes/header.php';

if ($_SESSION['user']['role'] !== 'kasir') {
  header('Location: ../index.php');
  exit;
}

$namaKasir = htmlspecialchars($_SESSION['user']['nama']);
?>

<div class="container py-5">
  <div class="text-center mb-4">
    <h2 class="fw-bold">Dashboard Kasir</h2>
    <p class="text-muted">Selamat datang, <?= $namaKasir; ?>! Silakan pilih menu kasir di bawah ini.</p>
  </div>

  <div class="row g-4">
    <!-- Menu: Checkout Tunai -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-body text-center">
          <i class="bi bi-cash-stack display-4 text-primary mb-3"></i>
          <h5 class="card-title fw-semibold">Checkout Tunai</h5>
          <p class="card-text">Input transaksi pembayaran siswa yang dilakukan secara langsung (tunai).</p>
          <a href="checkout_tunai.php" class="btn btn-primary mt-2">Buka</a>
        </div>
      </div>
    </div>

    <!-- Menu: Data Siswa -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-body text-center">
          <i class="bi bi-people display-4 text-success mb-3"></i>
          <h5 class="card-title fw-semibold">Data Siswa</h5>
          <p class="card-text">Lihat daftar siswa yang terdaftar di sistem untuk keperluan transaksi.</p>
          <a href="data_siswa.php" class="btn btn-success mt-2">Lihat</a>
        </div>
      </div>
    </div>

    <!-- Menu: Riwayat Transaksi -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-body text-center">
          <i class="bi bi-receipt-cutoff display-4 text-warning mb-3"></i>
          <h5 class="card-title fw-semibold">Riwayat Transaksi</h5>
          <p class="card-text">Cek semua pembayaran baik yang dilakukan online maupun tunai.</p>
          <a href="transaksi.php" class="btn btn-warning mt-2 text-white">Lihat</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
