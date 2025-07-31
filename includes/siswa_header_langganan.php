<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$user = $_SESSION['user'] ?? null;

if (!$user || $user['role'] !== 'siswa') {
  header("Location: ../auth/login.php");
  exit;
}
?>
<!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
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
    .nav-link {
      margin-left: 1rem;
      transition: 0.3s;
    }
    .nav-link:hover {
      opacity: 0.9;
      color: #fdfdfdff;
      transform: translateY(-2px);
       text-decoration-thickness: 2px;
    }
    .nav-link.active {
      font-weight: bold;
      border-bottom: 2px solid #ffc107;
    }
    .navbar .nav-item {
        list-style-type: none;
    }

  </style>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Siswa - BimbelAja</title>
  <link rel="stylesheet" href="/BimbelAja/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="/BimbelAja/assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">  <div class="container">
    <a class="navbar-brand" href="/BimbelAja/siswa/dashboard.php">BimbelAja</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
     <ul class="navbar-nav ms-auto me-3 d-flex align-items-center">
        <li class="nav-item">
          <a class="nav-link" href="/BimbelAja/siswa/dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/BimbelAja/siswa/materi.php">Materi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/BimbelAja/siswa/soal.php">latihan</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/BimbelAja/siswa/forum.php">Forum</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/BimbelAja/langganan/riwayat.php">Riwayat</a>
        </li>
    <!-- Notifikasi -->
        <li class="nav-item dropdown">
            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell-fill"></i>
    <!-- Badge jumlah notifikasi -->
     <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"> 3</span>
            </a>
  <ul class="dropdown-menu dropdown-menu-end">
    <li><h6 class="dropdown-header">Notifikasi</h6></li>
    <li><a class="dropdown-item" href="#">✅ Materi baru ditambahkan</a></li>
    <li><a class="dropdown-item" href="#">⏰ Langganan kamu hampir habis</a></li>
    <li><a class="dropdown-item" href="#">🎉 Promo langganan tersedia</a></li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item text-center text-primary" href="#">Lihat Semua</a></li>
  </ul>
</li>
      </ul>
        <li class="nav-item dropdown colour-white">
          <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">
            <?= htmlspecialchars($user['nama'] ?? 'Siswa') ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="/BimbelAja/siswa/profil.php">Profil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="/BimbelAja/auth/logout.php">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
