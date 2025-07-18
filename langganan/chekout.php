<?php
session_start();
include '../config/database.php';

$user_id = $_SESSION['user']['id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Langganan - BimbelAja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            background: linear-gradient(180deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
        }  
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            font-size: 0.95rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .sidebar .nav-link i {
            min-width: 25px;
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .form-select, .form-control {
            height: 45px;
            border-radius: 8px;
            border: 1px solid #ced4da;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 500;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>

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

<div class="container-fluid" style="margin-top: 70px;">
  <div class="row">
    <!-- Sidebar -->
    <nav class="sidebar">
      <div class="sidebar-sticky pt-3">
        <h5 class="px-4 pb-3 pt-1 fw-bold text-white">MENU LANGGANAN</h5>
        <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link active" href="/BimbelAja/admin/dashboard.php">
              <i class="bi bi-house-door"></i> Kembali ke Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/BimbelAja/langganan/chekout.php">
              <i class="bi bi-cart-check"></i> Checkout Langganan
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/BimbelAja/langganan/riwayat.php">
              <i class="bi bi-clock-history"></i> Riwayat Langganan
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/BimbelAja/langganan/paket.php">
              <i class="bi bi-tags"></i> Paket
            </a>
          </li>
        </ul>
        <h5 class="px-4 pb-2 pt-4 fw-bold text-white">MENU ADMIN</h5>
        <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link" href="/BimbelAja/admin/kelola_materi.php">
              <i class="bi bi-book"></i> Kelola Materi
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/BimbelAja/admin/kelola_user.php">
              <i class="bi bi-people"></i> Kelola User
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/BimbelAja/admin/statistik.php">
              <i class="bi bi-bar-chart"></i> Statistik
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/BimbelAja/admin/verifikasi_pembayaran.php">
              <i class="bi bi-check-circle"></i> Verifikasi Pembayaran
            </a>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h3 fw-bold text-primary">Checkout Langganan</h1>
      </div>

      <div class="row">
        <div class="col-lg-8">
          <div class="card p-4 mb-4">
            <form method="POST" action="">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="paket" class="form-label fw-bold">Pilih Paket</label>
                  <select name="paket" id="paket" class="form-select">
                    <option value="basic">Paket Basic</option>
                    <option value="premium">Paket Premium</option>
                  </select>
                </div>
                
                <div class="col-md-6">
                  <label for="jenjang" class="form-label fw-bold">Jenjang Pendidikan</label>
                  <input type="text" name="jenjang" id="jenjang" class="form-control" placeholder="Contoh: SMA, SMP">
                </div>
                
                <div class="col-md-6">
                  <label for="kelas" class="form-label fw-bold">Kelas</label>
                  <input type="text" name="kelas" id="kelas" class="form-control" placeholder="Contoh: 10, 11, 12">
                </div>
                
                <div class="col-12 mt-4">
                  <button type="submit" name="submit" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-cart-check me-2"></i> Lanjutkan Pembayaran
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
        
        <div class="col-lg-4">
          <div class="card p-4 mb-4">
            <h6 class="fw-bold mb-4 text-primary">
              <i class="bi bi-info-circle me-2"></i> Informasi Paket
            </h6>
            
            <div class="border-bottom pb-3 mb-3">
              <div class="d-flex justify-content-between mb-1">
                <span>Paket Basic</span>
                <span class="fw-bold">Rp 100.000/bln</span>
              </div>
              <small class="text-muted">Fitur Dasar: Materi Pelajaran, 6x Pertemuan/Minggu</small>
            </div>
            
            <div>
              <div class="d-flex justify-content-between mb-1">
                <span>Paket Premium</span>
                <span class="fw-bold">Rp 200.000/bln</span>
              </div>
              <small class="text-muted">Fitur Lengkap: Materi Premium, Video Pembelajaran, Konsultasi Guru, 10x Pertemuan/Minggu</small>
            </div>
          </div>
        </div>
      </div>

      <?php
      if (isset($_POST['submit'])) {
          $paket = $_POST['paket'];
          $jenjang = $_POST['jenjang'];
          $kelas = $_POST['kelas'];

          // Ambil info durasi dari database paket
          $result = mysqli_query($conn, "SELECT durasi, satuan_durasi FROM paket WHERE kategori = '$paket' LIMIT 1");
          $row = mysqli_fetch_assoc($result);

          $durasi = (int)$row['durasi'];
          $satuan = $row['satuan_durasi'];

          $mulai = date('Y-m-d');
          if ($satuan === 'bulan') {
              $akhir = date('Y-m-d', strtotime("+$durasi month"));
          } elseif ($satuan === 'tahun') {
              $akhir = date('Y-m-d', strtotime("+$durasi year"));
          } else {
              $akhir = $mulai; // fallback
          }

          $sql = "INSERT INTO langganan (user_id, paket, jenjang, kelas, tanggal_mulai, tanggal_berakhir)
                  VALUES ('$user_id', '$paket', '$jenjang', '$kelas', '$mulai', '$akhir')";

          if ($conn->query($sql)) {
              echo '<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                      <i class="bi bi-check-circle-fill me-2"></i> Langganan berhasil ditambahkan! Silakan lakukan pembayaran.
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
          } else {
              echo '<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                      <i class="bi bi-exclamation-triangle-fill me-2"></i> Error: ' . $conn->error . '
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
          }
      }
      ?>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

