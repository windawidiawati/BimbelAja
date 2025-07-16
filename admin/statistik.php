<?php
// Mulai session jika belum aktif
session_start();

// Cek apakah user adalah admin
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php'); 
    exit;
}

// Koneksi ke database
include '../config/database.php';

// Query untuk mengambil data statistik
$users_query = "SELECT COUNT(*) as total_users, 
                SUM(CASE WHEN role = 'siswa' THEN 1 ELSE 0 END) as siswa,
                SUM(CASE WHEN role = 'tutor' THEN 1 ELSE 0 END) as tutor,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin
                FROM users";
$users_result = $conn->query($users_query);
$users_data = $users_result->fetch_assoc();

$materi_query = "SELECT COUNT(*) as total_materi FROM materi";
$materi_result = $conn->query($materi_query);
$materi_data = $materi_result->fetch_assoc();

$kelas_query = "SELECT COUNT(*) as total_kelas FROM kelas WHERE status = 'aktif'";
$kelas_result = $conn->query($kelas_query);
$kelas_data = $kelas_result->fetch_assoc();

$aktivitas_query = "SELECT DATE_FORMAT(tanggal, '%Y-%m') as bulan, 
                   COUNT(*) as jumlah_aktivitas 
                   FROM aktivitas_pengguna 
                   GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
                   ORDER BY bulan DESC LIMIT 6";
$aktivitas_result = $conn->query($aktivitas_query);
$aktivitas_data = [];
while($row = $aktivitas_result->fetch_assoc()) {
    $aktivitas_data[] = $row;
}

$laporan_query = "SELECT id, judul, tanggal_buat, penulis FROM laporan ORDER BY tanggal_buat DESC LIMIT 5";
$laporan_result = $conn->query($laporan_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik - BimbelAja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 56px;
            left: 0;
            width: 250px;
            background-color: #0d6efd;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
        }
        .sidebar a:hover {
            background-color: #0056b3;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .stat-card {
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="../index.php">
            <i class="bi bi-mortarboard-fill me-2"></i>BimbelAja
        </a>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar">
    <h5 class="text-white text-center">Admin Panel</h5>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="kelola_materi.php">
                <i class="bi bi-file-earmark-text"></i> Kelola Materi
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="kelola_user.php">
                <i class="bi bi-person"></i> Kelola User
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="statistik.php">
                <i class="bi bi-bar-chart"></i> Statistik
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="verifikasi_pembayaran.php">
                <i class="bi bi-credit-card"></i> Verifikasi Pembayaran
            </a>
        </li>
    </ul>
</div>

<!-- Konten Utama -->
<div class="content">
    <h2 class="mb-4"><i class="bi bi-bar-chart me-2"></i>Statistik Aplikasi</h2>
    
    <!-- Ringkasan Statistik -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-primary text-white h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-people-fill"></i> Total Pengguna</h5>
                    <h1 class="display-5"><?= $users_data['total_users'] ?></h1>
                    <div class="d-flex justify-content-between">
                        <span>Siswa: <?= $users_data['siswa'] ?></span>
                        <span>Tutor: <?= $users_data['tutor'] ?></span>
                        <span>Admin: <?= $users_data['admin'] ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-success text-white h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-journal-bookmark-fill"></i> Total Materi</h5>
                    <h1 class="display-5"><?= $materi_data['total_materi'] ?></h1>
                    <p class="card-text">Materi pembelajaran tersedia</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-warning text-dark h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-layers-fill"></i> Kelas Aktif</h5>
                    <h1 class="display-5"><?= $kelas_data['total_kelas'] ?></h1>
                    <p class="card-text">Kelas sedang berjalan</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Grafik Statistik -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Distribusi Pengguna</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="userDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Aktivitas Pengguna (6 Bulan Terakhir)</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Laporan Terbaru -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="bi bi-file-earmark-text me-2"></i>Laporan Terbaru</h5>
                </div>
                <div class="card-body">
                    <?php if ($laporan_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Judul</th>
                                        <th>Penulis</th>
                                        <th>Tanggal Buat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($laporan = $laporan_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $laporan['id'] ?></td>
                                            <td><?= htmlspecialchars($laporan['judul']) ?></td>
                                            <td><?= htmlspecialchars($laporan['penulis']) ?></td>
                                            <td><?= date('d M Y', strtotime($laporan['tanggal_buat'])) ?></td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary">Lihat</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">Belum ada laporan tersedia</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script Chart -->
<script>
    // Chart Distribusi Pengguna
    const userCtx = document.getElementById('userDistributionChart').getContext('2d');
    const userChart = new Chart(userCtx, {
        type: 'pie',
        data: {
            labels: ['Siswa', 'Tutor', 'Admin'],
            datasets: [{
                data: [
                    <?= $users_data['siswa'] ?>, 
                    <?= $users_data['tutor'] ?>, 
                    <?= $users_data['admin'] ?>
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
        }
    });

    // Chart Aktivitas Pengguna
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(activityCtx, {
        type: 'bar',
        data: {
            labels: [
                <?php foreach(array_reverse($aktivitas_data) as $data): ?>
                    '<?= date('M Y', strtotime($data['bulan'])) ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                label: 'Jumlah Aktivitas',
                data: [
                    <?php foreach(array_reverse($aktivitas_data) as $data): ?>
                        <?= $data['jumlah_aktivitas'] ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: 'rgba(153, 102, 255, 0.7)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
</body>
</html>
