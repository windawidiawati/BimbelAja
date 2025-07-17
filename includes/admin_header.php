<?php
// Mulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Cek apakah user adalah admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}

$current_page = basename($_SERVER['SCRIPT_NAME']);
$role = $_SESSION['user']['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f9f9f9;
    }

    .navbar-brand {
      font-weight: bold;
      font-size: 1.5rem;
    }

    .nav-link.active {
      font-weight: bold;
      border-bottom: 2px solid #ffc107;
    }

    .sidebar {
      position: fixed;
      top: 56px;
      left: 0;
      width: 250px;
      height: 100%;
      background-color: #0d6efd;
      padding-top: 20px;
      overflow-y: auto;
    }

    .sidebar a {
      color: white;
      padding: 10px 20px;
      display: block;
      text-decoration: none;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: #0056b3;
    }

    .content {
      margin-left: 250px;
      padding: 20px;
    }

    @media (max-width: 768px) {
      .sidebar {
        display: none;
      }
      .content {
        margin-left: 0;
        padding: 20px;
      }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand me-3" href="/BimbelAja/index.php">
      <i class="bi bi-mortarboard-fill me-2"></i>BimbelAja
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <!-- Menu utama (khusus tampilan kecil) -->
      <ul class="navbar-nav me-auto d-lg-none">
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'dashboard.php') ? 'active' : '' ?>" href="/BimbelAja/admin/dashboard.php">
            <i class="bi bi-speedometer2 me-1"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'kelola_materi.php') ? 'active' : '' ?>" href="/BimbelAja/admin/kelola_materi.php">
            <i class="bi bi-file-earmark-text me-1"></i> Kelola Materi
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'kelola_user.php') ? 'active' : '' ?>" href="/BimbelAja/admin/kelola_user.php">
            <i class="bi bi-person me-1"></i> Kelola User
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'kelola_paket.php') ? 'active' : '' ?>" href="/BimbelAja/admin/kelola_paket.php">
            <i class="bi bi-boxes me-1"></i> Kelola Paket
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'verifikasi_pembayaran.php') ? 'active' : '' ?>" href="/BimbelAja/admin/verifikasi_pembayaran.php">
            <i class="bi bi-credit-card me-1"></i> Verifikasi Pembayaran
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'statistik.php') ? 'active' : '' ?>" href="/BimbelAja/admin/statistik.php">
            <i class="bi bi-bar-chart me-1"></i> Statistik
          </a>
        </li>
      </ul>

      <!-- Menu profil -->
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link text-white <?= ($current_page === 'profil.php') ? 'active' : '' ?>" href="/BimbelAja/admin/profil.php">
            <i class="bi bi-person-circle me-1"></i>Profil
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="/BimbelAja/auth/logout.php">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Sidebar untuk layar besar -->
<div class="sidebar d-none d-lg-block">
  <a class="<?= ($current_page === 'dashboard.php') ? 'active' : '' ?>" href="/BimbelAja/admin/dashboard.php">
    <i class="bi bi-speedometer2 me-2"></i> Dashboard
  </a>
  <a class="<?= ($current_page === 'kelola_materi.php') ? 'active' : '' ?>" href="/BimbelAja/admin/kelola_materi.php">
    <i class="bi bi-file-earmark-text me-2"></i> Kelola Materi
  </a>
  <a class="<?= ($current_page === 'kelola_user.php') ? 'active' : '' ?>" href="/BimbelAja/admin/kelola_user.php">
    <i class="bi bi-person me-2"></i> Kelola User
  </a>
  <a class="<?= ($current_page === 'kelola_paket.php') ? 'active' : '' ?>" href="/BimbelAja/admin/kelola_paket.php">
    <i class="bi bi-boxes me-2"></i> Kelola Paket
  </a>
  <a class="<?= ($current_page === 'verifikasi_pembayaran.php') ? 'active' : '' ?>" href="/BimbelAja/admin/verifikasi_pembayaran.php">
    <i class="bi bi-credit-card me-2"></i> Verifikasi Pembayaran
  </a>
  <a class="<?= ($current_page === 'statistik.php') ? 'active' : '' ?>" href="/BimbelAja/admin/statistik.php">
    <i class="bi bi-bar-chart me-2"></i> Statistik
  </a>
</div>
