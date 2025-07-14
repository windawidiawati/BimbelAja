<?php
// Mulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Include koneksi database
include_once __DIR__ . '/../config/database.php'; // Pastikan path ini sesuai struktur folder kamu

// Ambil nama file halaman sekarang
$current_page = basename($_SERVER['SCRIPT_NAME']);

// Ambil role user jika login
$role = $_SESSION['user']['role'] ?? null;

// Cek status langganan siswa (jika role siswa)
$dashboard_link = "/BimbelAja/index.php";
if ($role === 'siswa' && isset($_SESSION['user']['id'])) {
  $userId = $_SESSION['user']['id'];

  // Cek koneksi
  if ($conn) {
    $cekLangganan = mysqli_query($conn, "SELECT * FROM langganan WHERE user_id = $userId AND status = 'aktif' AND tanggal_berakhir >= CURDATE()");
    if (mysqli_num_rows($cekLangganan) > 0) {
      $dashboard_link = "/BimbelAja/siswa/dashboard.php";
    }
  }
} elseif ($role && $role !== 'siswa') {
  $dashboard_link = "/BimbelAja/$role/dashboard.php";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BimbelAja</title>

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
    }
    .nav-link.active {
      font-weight: bold;
      border-bottom: 2px solid #ffc107;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="/BimbelAja/index.php">
      <i class="bi bi-mortarboard-fill me-2"></i>BimbelAja
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto align-items-center">
        <?php if (isset($_SESSION['user'])): ?>
          <!-- Menu untuk user yang login -->
          <li class="nav-item">
            <a class="nav-link text-white <?= ($current_page === 'dashboard.php') ? 'active' : '' ?>" href="<?= $dashboard_link ?>">
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
        <?php else: ?>
          <!-- Menu untuk pengunjung (belum login) -->
          <li class="nav-item">
            <a class="nav-link text-white <?= ($current_page === 'index.php') ? 'active' : '' ?>" href="/BimbelAja/index.php">
              <i class="bi bi-house-door me-1"></i>Beranda
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white <?= ($current_page === 'paket.php') ? 'active' : '' ?>" href="/BimbelAja/langganan/paket.php">
              <i class="bi bi-tags me-1"></i>Langganan
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white <?= ($current_page === 'login.php') ? 'active' : '' ?>" href="/BimbelAja/auth/login.php">
              <i class="bi bi-box-arrow-in-right me-1"></i>Login
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
