<?php
// Mulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Cek apakah user adalah tutor
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php');
  exit;
}

$current_page = basename($_SERVER['SCRIPT_NAME']);
// $role = $_SESSION['user']['role'] ?? null;
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
        <li class="nav-item"><a class="nav-link <?= ($current_page === 'dashboard.php') ? 'active' : '' ?>" href="..tutor/dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
        
        <li class="nav-item"><a class="nav-link <?= ($current_page === 'unggah_materi.php') ? 'active' : '' ?>" href="..tutor/unggah_materi.php"><i class="bi bi-upload me-1"></i> Unggah Materi</a></li>
        
        <li class="nav-item"><a class="nav-link <?= ($current_page === 'buat_soal.php') ? 'active' : '' ?>" href="..tutor/buat_soal.php"><i class="bi bi-pencil-square me-1"></i> Buat Soal</a></li>
        <li class="nav-item"><a class="nav-link <?= ($current_page === 'jawaban_siswa.php') ? 'active' : '' ?>" href="..tutor/jawaban_siswa.php"><i class="bi bi-calendar-event me-1"></i> Jawaban Siswa</a></li>
        <li class="nav-item"><a class="nav-link <?= ($current_page === 'jadwal_offline.php') ? 'active' : '' ?>" href="..tutor/jadwal_offline.php"><i class="bi bi-calendar3 me-1"></i> Jadwal Offline</a></li>
        <li class="nav-item"><a class="nav-link <?= ($current_page === 'absensi.php') ? 'active' : '' ?>" href="..tutor/absensi.php"><i class="bi bi-list-check me-1"></i> Absensi</a></li>
        <li class="nav-item"><a class="nav-link <?= ($current_page === 'rekap_absensi.php') ? 'active' : '' ?>" href="..tutor/rekap_absensi.php"><i class="bi bi-clipboard-data me-1"></i> Rekap Absensi</a></li>
        <!-- <li class="nav-item"><a class="nav-link <?= ($current_page === 'forum.php') ? 'active' : '' ?>" href="..tutor/forum.php"><i class="bi bi-chat-dots me-1"></i> Forum</a></li> -->
        <li class="nav-item"><a class="nav-link <?= ($current_page === 'data_siswa.php') ? 'active' : '' ?>" href="../tutor/data_siswa.php"><i class="bi bi-people me-1"></i> Data Siswa</a></li>
      </ul>

      <!-- Menu profil -->
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link text-white <?= ($current_page === 'profil.php') ? 'active' : '' ?>" href="../tutor/profil.php"><i class="bi bi-person-circle me-1"></i>Profil</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="../auth/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Sidebar untuk layar besar -->
<div class="sidebar d-none d-lg-block">
  <a class="<?= ($current_page === 'dashboard.php') ? 'active' : '' ?>" href="../tutor/dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
  <a class="<?= ($current_page === 'unggah_materi.php') ? 'active' : '' ?>" href="../tutor/unggah_materi.php"><i class="bi bi-upload me-2"></i> Unggah Materi</a>
  <a class="<?= ($current_page === 'buat_soal.php') ? 'active' : '' ?>" href="../tutor/buat_soal.php"><i class="bi bi-pencil-square me-2"></i> Buat Soal</a>
  <a class="<?= ($current_page === 'jawaban_siswa.php') ? 'active' : '' ?>" href="../tutor/jawaban_siswa.php"><i class="bi bi-calendar-event me-2"></i> Jawaban Siswa</a>
  <a class="<?= ($current_page === 'jadwal_offline.php') ? 'active' : '' ?>" href="../tutor/jadwal_offline.php"><i class="bi bi-calendar3 me-2"></i> Jadwal Offline</a>
  <a class="<?= ($current_page === 'absensi_offline.php') ? 'active' : '' ?>" href="../tutor/absensi_offline.php"><i class="bi bi-list-check me-2"></i> Absensi</a>
  <a class="<?= ($current_page === 'rekap_absensi.php') ? 'active' : '' ?>" href="../tutor/rekap_absensi.php"><i class="bi bi-clipboard-data me-2"></i> Rekap Absensi</a>
  <!-- <a class="<?= ($current_page === 'forum.php') ? 'active' : '' ?>" href="../tutor/forum.php"><i class="bi bi-chat-dots me-2"></i> Forum</a> -->
  <a class="<?= ($current_page === 'data_siswa.php') ? 'active' : '' ?>" href="../tutor/data_siswa.php"><i class="bi bi-people me-2"></i> Data Siswa</a>
</div>
