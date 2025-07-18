<?php
<<<<<<< HEAD
session_start();
include '../config/database.php';
=======
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

=======
if (session_status() == PHP_SESSION_NONE) session_start();
>>>>>>> 7ce4a5c3315c8c9afd5ceefe16cabb340ebdc2f9
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php");
    exit;
}

include_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user']['id'];
$query = mysqli_query($conn, "SELECT * FROM pembayaran WHERE user_id = $user_id ORDER BY tanggal DESC");
>>>>>>> origin
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Langganan - BimbelAja</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background-color: #0d6efd;
            color: white;
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
              <th>ID</th>
              <th>User ID</th>
              <th>Paket</th>
              <th>Jenjang</th>
              <th>Kelas</th>
              <th>Tanggal Mulai</th>
              <th>Tanggal Berakhir</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $query = "SELECT * FROM langganan ORDER BY created_at DESC";
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
              echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['user_id']}</td>
                <td>{$row['paket']}</td>
                <td>{$row['jenjang']}</td>
                <td>{$row['kelas']}</td>
                <td>{$row['tanggal_mulai']}</td>
                <td>{$row['tanggal_berakhir']}</td>
                <td>{$row['status']}</td>
              </tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
=======
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Pembayaran | BimbelAja</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h2 class="mb-4">Riwayat Pembayaran</h2>
  <table class="table table-bordered">
    <thead class="table-light">
      <tr>
        <th>No</th>
        <th>Kode Bayar</th>
        <th>Nama Paket</th>
        <th>Harga</th>
        <th>Status</th>
        <th>Bukti</th>
        <th>Tanggal</th>
      </tr>
    </thead>
    <tbody>
      <?php if (mysqli_num_rows($query) > 0): $no = 1; ?>
        <?php while ($row = mysqli_fetch_assoc($query)): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><strong><?= htmlspecialchars($row['kode_bayar']) ?></strong></td>
            <td><?= htmlspecialchars($row['paket']) ?></td>
            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
            <td>
              <?php
                $status = strtolower($row['status']);
                $badge = match($status) {
                  'lunas' => 'success',
                  'pending' => 'warning',
                  'menunggu kasir' => 'primary',
                  'ditolak' => 'danger',
                  default => 'secondary',
                };
              ?>
              <span class="badge bg-<?= $badge ?>"><?= ucfirst($status) ?></span>
            </td>
            <td>
              <?php if ($row['metode'] !== 'tunai' && $row['status'] === 'pending'): ?>
                <a href="upload_bukti.php?kode=<?= urlencode($row['kode_bayar']) ?>" class="btn btn-sm btn-info">Upload</a>
              <?php elseif (!empty($row['bukti_transfer'])): ?>
                <a href="../uploads/<?= htmlspecialchars($row['bukti_transfer']) ?>" target="_blank" class="btn btn-sm btn-success">Lihat</a>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="7" class="text-center">Belum ada riwayat pembayaran.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
>>>>>>> origin
</body>
</html>
