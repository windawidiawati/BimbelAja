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

// Daftar halaman kelola untuk dropdown
$kelola_pages = [
  'kelola_materi.php' => ['icon' => 'file-earmark-text', 'title' => 'Kelola Materi'],
  'kelola_user.php' => ['icon' => 'person', 'title' => 'Kelola User'],
  'kelola_paket.php' => ['icon' => 'boxes', 'title' => 'Kelola Paket'],
  'kelola_soal.php' => ['icon' => 'journal-text', 'title' => 'Kelola Soal'],
  'kelola_latihan.php' => ['icon' => 'pencil-square', 'title' => 'Kelola Latihan'],
  'kelola_jadwal_offline.php' => ['icon' => 'calendar-event', 'title' => 'Kelola Jadwal Offline'],
  'kelola_absensi.php' => ['icon' => 'clipboard-check', 'title' => 'Kelola Absensi'],
  'kelola_jawaban_siswa.php' => ['icon' => 'clipboard-check', 'title' => 'Kelola Jawaban Siswa'],
  'kelola_absensi_tutor.php' => ['icon' => 'clipboard-check', 'title' => 'Kelola Absensi Tutor'],
];

// Cek apakah halaman saat ini adalah salah satu halaman kelola
$is_kelola_page = in_array($current_page, array_keys($kelola_pages));
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
      z-index: 1000;
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

    /* Style untuk dropdown sidebar */
    .sidebar-dropdown {
      position: relative;
    }
    
    .sidebar-dropdown-menu {
      background-color: #0a58ca;
      display: none;
      padding: 0;
      margin: 0;
      width: 100%;
    }
    
    .sidebar-dropdown.active .sidebar-dropdown-menu {
      display: block;
    }
    
    .sidebar-dropdown-item {
      color: white;
      padding: 8px 20px 8px 45px;
      display: block;
      text-decoration: none;
    }
    
    .sidebar-dropdown-item:hover,
    .sidebar-dropdown-item.active {
      background-color: #084298;
      color: white;
    }
    
    .sidebar-dropdown-toggle {
      cursor: pointer;
      position: relative;
    }
    
    .sidebar-dropdown-toggle::after {
      content: "\f282";
      font-family: "bootstrap-icons";
      border: none;
      font-size: 0.8rem;
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      transition: transform 0.3s;
    }
    
    .sidebar-dropdown.active .sidebar-dropdown-toggle::after {
      transform: translateY(-50%) rotate(90deg);
    }

    @media (max-width: 768px) {
      .sidebar {
        display: none;
      }
      .content {
        margin-left: 0;
        padding: 20px;
      }
      
      /* Style untuk dropdown di menu mobile */
      .navbar-nav .dropdown-menu {
        background-color: transparent;
        padding-left: 20px;
      }
      
      .navbar-nav .dropdown-item {
        padding: 8px 15px;
      }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
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
          <a class="nav-link <?= ($current_page === 'dashboard.php') ? 'active' : '' ?>" href="../admin/dashboard.php">
            <i class="bi bi-speedometer2 me-1"></i> Dashboard
          </a>
        </li>
        
        <!-- Dropdown Data Kelola untuk mobile -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $is_kelola_page ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-database me-1"></i> Data Kelola
          </a>
          <ul class="dropdown-menu">
            <?php foreach ($kelola_pages as $page => $data): ?>
              <li>
                <a class="dropdown-item <?= ($current_page === $page) ? 'active' : '' ?>" href="../admin/<?= $page ?>">
                  <i class="bi bi-<?= $data['icon'] ?> me-1"></i> <?= $data['title'] ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </li>
        
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'verifikasi_pembayaran.php') ? 'active' : '' ?>" href="../admin/verifikasi_pembayaran.php">
            <i class="bi bi-credit-card me-1"></i> Verifikasi Pembayaran
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'statistik.php') ? 'active' : '' ?>" href="../admin/statistik.php">
            <i class="bi bi-bar-chart me-1"></i> Statistik
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'laporan_transaksi.php') ? 'active' : '' ?>" href="../admin/laporan_transaksi.php">
            <i class="bi bi-cash-stack me-2"></i> Laporan Transaksi
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($current_page === 'laporan_absensi.php') ? 'active' : '' ?>" href="../admin/laporan_absensi.php">
            <i class="bi bi-calendar-check me-2"></i> Laporan Absensi
          </a>
        </li>
      </ul>

      <!-- Menu profil -->
<ul class="navbar-nav ms-auto">
  <li class="nav-item">
    <a class="nav-link text-white" href="Panduan_BIMBELAJA_RoleAdmin.pdf" target="_blank">
      <i class="bi bi-book me-1"></i>Buku Panduan
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link text-white <?= ($current_page === 'profil.php') ? 'active' : '' ?>" href="../admin/profil.php">
      <i class="bi bi-person-circle me-1"></i>Profil
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link text-white" href="../auth/logout.php">
      <i class="bi bi-box-arrow-right me-1"></i>Logout
    </a>
  </li>
</ul>
    </div>
  </div>
</nav>

<!-- Sidebar untuk layar besar -->
<div class="sidebar d-none d-lg-block">
  <a class="<?= ($current_page === 'dashboard.php') ? 'active' : '' ?>" href="../admin/dashboard.php">
    <i class="bi bi-speedometer2 me-2"></i> Dashboard
  </a>
  
  <!-- Dropdown Data Kelola untuk desktop -->
  <div class="sidebar-dropdown <?= $is_kelola_page ? 'active' : '' ?>">
    <a class="sidebar-dropdown-toggle d-block <?= $is_kelola_page ? 'active' : '' ?>" href="#" id="dataKelolaDropdown">
      <i class="bi bi-database me-2"></i> Data Kelola
    </a>
    <div class="sidebar-dropdown-menu">
      <?php foreach ($kelola_pages as $page => $data): ?>
        <a class="sidebar-dropdown-item <?= ($current_page === $page) ? 'active' : '' ?>" href="../admin/<?= $page ?>">
          <i class="bi bi-<?= $data['icon'] ?> me-1"></i> <?= $data['title'] ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
  
  <a class="<?= ($current_page === 'verifikasi_pembayaran.php') ? 'active' : '' ?>" href="../admin/verifikasi_pembayaran.php">
    <i class="bi bi-credit-card me-2"></i> Verifikasi Pembayaran
  </a>
  <a class="<?= ($current_page === 'statistik.php') ? 'active' : '' ?>" href="../admin/statistik.php">
    <i class="bi bi-bar-chart me-2"></i> Statistik
  </a>
  <a class="<?= ($current_page === 'laporan_transaksi.php') ? 'active' : '' ?>" href="../admin/laporan_transaksi.php">
    <i class="bi bi-cash-stack me-2"></i> Laporan Transaksi
  </a>
  <a class="<?= ($current_page === 'data_siswa.php') ? 'active' : '' ?>" href="../admin/data_siswa.php">
    <i class="bi bi-cash-stack me-2"></i> Data Siswa
  </a>
  <a class="<?= ($current_page === 'laporan_absensi.php') ? 'active' : '' ?>" href="../admin/laporan_absensi.php">
    <i class="bi bi-calendar-check me-2"></i> Laporan Absensi
  </a>
  <a class="<?= ($current_page === 'pengaturan.php') ? 'active' : '' ?>" href="../admin/pengaturan.php">
    <i class="bi bi-gear me-2"></i> Pengaturan 
  </a>
</div>

<!-- Konten utama -->
<div class="content">
  <!-- Konten halaman akan ditampilkan di sini -->
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Script sederhana untuk toggle dropdown
  document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggle = document.getElementById('dataKelolaDropdown');
    const dropdown = document.querySelector('.sidebar-dropdown');
    
    if (dropdownToggle && dropdown) {
      dropdownToggle.addEventListener('click', function(e) {
        e.preventDefault();
        dropdown.classList.toggle('active');
      });
    }
    
    // Jika halaman aktif adalah salah satu halaman kelola, buka dropdown
    const isKelolaPage = <?= $is_kelola_page ? 'true' : 'false' ?>;
    if (isKelolaPage && dropdown) {
      dropdown.classList.add('active');
    }
  });
</script>
</body>
</html>