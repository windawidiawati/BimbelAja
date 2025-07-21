<?php
if (session_status() == PHP_SESSION_NONE) session_start();
session_start();
include '../config/database.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php");
    exit;
}

include_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user']['id'];

// Ambil semua transaksi user
$query = mysqli_query($conn, "SELECT * FROM pembayaran WHERE user_id = '$user_id' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Riwayat Transaksi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<<<<<<< HEAD
<div class="container py-5">
    <h3 class="mb-4">Riwayat Transaksi</h3>
    <table class="table table-bordered">
        <thead>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="/BimbelAja/index.php">
      <i class="bi bi-mortarboard-fill me-2"></i>BimbelAja
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto align-items-center">
        <?php if (isset($_SESSION['user'])): ?>
          <li class="nav-item">
            <a class="nav-link text-white <?= ($current_page === 'dashboard.php') ? 'active' : '' ?>" href="/BimbelAja/<?= $role ?>/dashboard.php">
              <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white <?= ($current_page === 'profil.php') ? 'active' : '' ?>" href="/BimbelAja/<?= $role ?>/profil.php">
              <i class="bi bi-person-circle me-1"></i>Profil
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white" href="/BimbelAja/auth/logout.php">
              <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid" style="margin-top: 70px;">
  <div class="row">
    <!-- Sidebar -->
    <nav class="sidebar">
      <div class="sidebar-sticky pt-3">
        <h5 class="px-4 pb-3 pt-1 fw-bold text-white">MENU LANGGAANAN</h5>
        <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link" href="/BimbelAja/langganan/checkout.php">
              <i class="bi bi-cart-check"></i> Checkout Langganan
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="/BimbelAja/langganan/riwayat.php">
              <i class="bi bi-clock-history"></i> Riwayat Langganan
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/BimbelAja/langganan/paket.php">
              <i class="bi bi-tags"></i> Paket
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/BimbelAja/admin/dashboard.php">
              <i class="bi bi-house-door"></i> Kembali ke Dashboard
            </a>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h3 fw-bold text-primary">Data Langganan</h1>
      </div>

      <div class="card p-4 mb-4">
        <h5 class="fw-bold">Daftar Data Langganan</h5>
        <table>
          <thead>
            <tr>
                <th>Paket</th>
                <th>Harga</th>
                <th>Metode</th>
                <th>Kode Unik</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($query) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['paket']) ?></td>
                        <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td><?= ucfirst($row['metode']) ?></td>
                        <td><?= $row['kode_unik'] ?: '-' ?></td>
                        <td>
                            <?php
                            $badgeClass = match($row['status']) {
                                'lunas' => 'bg-success',
                                'pending', 'menunggu_kasir' => 'bg-warning',
                                'ditolak' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= ucfirst($row['status']) ?></span>
                        </td>
                        <td><?= $row['tanggal'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">Belum ada transaksi</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
