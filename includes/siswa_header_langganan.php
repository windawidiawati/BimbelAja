<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$user = $_SESSION['user'] ?? null;

if (!$user || $user['role'] !== 'siswa') {
  header("Location: BimbelAja/auth/login.php");
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
      color: #ffffff !important; /* Warna teks putih */
      background-color: rgba(255, 255, 255, 0.1); /* Sedikit transparan putih */
      border-radius: 4px;
    }
    .navbar .nav-item {
        list-style-type: none;
    }

    /* Untuk hover pada link aktif */
    .nav-link.active:hover {
      background-color: rgba(255, 255, 255, 0.2); /* Lebih terang saat hover */
    }
</style>

  </styl>
<!DOCTYPE html>
<html lang="id">
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand" href="/BimbelAja/siswa/dashboard.php">BimbelAja</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto me-3 d-flex align-items-center">
        <?php 
        $current_page = basename($_SERVER['PHP_SELF']); 
        ?>
        
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'dashboard.php') ? 'active' : '' ?>" href="../siswa/dashboard.php">
            <i class="bi bi-house-door me-1"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'materi.php') ? 'active' : '' ?>" href="../siswa/materi.php">
            <i class="bi bi-journal-bookmark me-1"></i> Materi
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'soal.php') ? 'active' : '' ?>" href="../siswa/soal.php">
            <i class="bi bi-pencil-square me-1"></i> Latihan
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'riwayat.php') ? 'active' : '' ?>" href="../langganan/riwayat.php">
            <i class="bi bi-clock-history me-1"></i> Riwayat
          </a>
        </li>

        <!-- Notifikasi -->
        <li class="nav-item dropdown">
          <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-bell-fill"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
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
        
        <!-- Profile -->
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'profil.php') ? 'active' : '' ?>" href="../siswa/profil.php">
            <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($user['nama'] ?? 'Siswa') ?>
          </a>
        </li>
        <li class="nav-item">
  <a class="nav-link <?= ($current_page === 'logout.php') ? 'active' : '' ?>" href="../auth/logout.php">
    <i class="bi bi-box-arrow-right me-1"></i>Logout
  </a>
</li>
</ul>
    </div>
  </div>
</nav>

