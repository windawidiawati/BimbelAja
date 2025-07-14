<?php
include '../config/database.php'; // tambahkan .php
session_start(); // Mulai session jika belum aktif
$current_page = basename($_SERVER['SCRIPT_NAME']); // Ambil nama file halaman sekarang
$role = $_SESSION['user']['role'] ?? null; // Ambil role user jika login
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kelola Materi & Soal</title>
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
    .sidebar {
      height: 100vh;
      position: fixed;
      top: 56px; /* Tinggi navbar */
      left: 0;
      width: 250px;
      background-color: #0d6efd; /* Warna yang sama dengan navbar */
      padding-top: 20px;
    }
    .sidebar a {
      color: white;
    }
    .sidebar a:hover {
      background-color: #0056b3; /* Warna hover yang lebih gelap */
    }
    .content {
      margin-left: 250px; /* Sesuaikan dengan lebar sidebar */
      padding: 20px;
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
        <?php else: ?>
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

<!-- Sidebar -->
<div class="sidebar">
  <h5 class="text-white text-center">Admin Panel</h5>
  <ul class="nav flex-column">
    <li class="nav-item">
      <a class="nav-link <?= ($current_page === 'dashboard.php') ? 'active' : '' ?>" href="/BimbelAja/admin/dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($current_page === 'kelola_materi.php') ? 'active' : '' ?>" href="/BimbelAja/admin/kelola_materi.php">
        <i class="bi bi-file-earmark-text"></i> Kelola Materi
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($current_page === 'kelola_user.php') ? 'active' : '' ?>" href="/BimbelAja/admin/kelola_user.php">
        <i class="bi bi-person"></i> Kelola User
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($current_page === 'statistik.php') ? 'active' : '' ?>" href="/BimbelAja/admin/statistik.php">
        <i class="bi bi-bar-chart"></i> Statistik
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($current_page === 'payment.php') ? 'active' : '' ?>" href="/BimbelAja/admin/payment.php">
        <i class="bi bi-credit-card"></i> Pembayaran
      </a>
    </li>
  </ul>
</div>

<!-- Konten Halaman Kelola Materi -->
<div class="content">
  <h1 class="text-2xl font-bold mb-4">Kelola Materi & Soal</h1>

  <table class="table table-bordered bg-white rounded shadow">
    <thead>
      <tr class="table-primary">
        <th>Judul</th>
        <th>Deskripsi</th>
        <th>File</th>
        <th>Kelas</th>
        <th>Tipe</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $query = mysqli_query($conn, "SELECT * FROM materi ORDER BY created_at DESC");
      while ($row = mysqli_fetch_assoc($query)) {
        echo "<tr>";
        echo "<td>{$row['judul']}</td>";
        echo "<td>" . substr($row['deskripsi'], 0, 50) . "...</td>";
        echo "<td><a href='../uploads/{$row['file']}' target='_blank' class='text-primary'>Lihat</a></td>";
        echo "<td>{$row['kelas_id']}</td>";
        echo "<td>{$row['tipe_file']}</td>";
        echo "<td>
                <span class='badge " . 
                  ($row['status'] == 'diterima' ? 'bg-success' :
                   ($row['status'] == 'ditolak' ? 'bg-danger' : 'bg-warning')) . 
                "'>{$row['status']}</span>
              </td>";
        echo "<td>
                <div class='btn-group' role='group'>
                  <form action='proses_materi.php' method='POST' class='d-inline'>
                    <input type='hidden' name='id' value='{$row['id']}'>
                    <button name='setujui' class='btn btn-success btn-sm' onclick='return confirm(\"Setujui materi ini?\")'>Setujui</button>
                  </form>
                  <form action='proses_materi.php' method='POST' class='d-inline'>
                    <input type='hidden' name='id' value='{$row['id']}'>
                    <button name='tolak' class='btn btn-danger btn-sm' onclick='return confirm(\"Tolak materi ini?\")'>Tolak</button>
                  </form>
                  <form action='proses_materi.php' method='POST' class='d-inline'>
                    <input type='hidden' name='id' value='{$row['id']}'>
                    <button name='hapus' class='btn btn-secondary btn-sm' onclick='return confirm(\"Hapus materi ini?\")'>Hapus</button>
                  </form>
                </div>
              </td>";
        echo "</tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
