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

// Ambil data pembayaran
$query = "SELECT p.*, u.username, u.nama 
          FROM pembayaran p 
          JOIN users u ON p.user_id = u.id 
          ORDER BY p.tanggal DESC";
$result = $conn->query($query);

// Menangani aksi hapus
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $deleteQuery = "DELETE FROM pembayaran WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: verifikasi_pembayaran.php"); // Redirect setelah hapus
    exit;
}

// Menangani aksi edit status
if (isset($_POST['edit_status']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $updateQuery = "UPDATE pembayaran SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: data_verifikasi.php"); // Redirect setelah edit
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pembayaran - BimbelAja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
        .status-lunas {
            color: green;
            font-weight: bold;
        }
        .status-ditolak {
            color: red;
            font-weight: bold;
        }
        .status-pending {
            color: orange;
            font-weight: bold;
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
            <a class="nav-link" href="statistik.php">
                <i class="bi bi-bar-chart"></i> Statistik
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="verifikasi_pembayaran.php">
                <i class="bi bi-credit-card"></i> Verifikasi Pembayaran
            </a>
        </li>
    </ul>
</div>

<!-- Konten Utama -->
<div class="content">
    <div class="card shadow">
        <div class="card-header bg-white">
            <h3 class="card-title mb-0">
                <i class="bi bi-credit-card me-2"></i> Data Verifikasi Pembayaran
            </h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama Siswa</th>
                            <th>Username</th>
                            <th>Paket</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['paket']) ?></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                    <td class="status-<?= $row['status'] ?>">
                                        <?= strtoupper($row['status']) ?>
                                    </td>
                                    <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
                                    <td>
                                        <form action="" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <select name="status" required>
                                                <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="lunas" <?= $row['status'] == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                                                <option value="ditolak" <?= $row['status'] == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                            </select>
                                            <button type="submit" name="edit_status" class="btn btn-sm btn-primary">Edit</button>
                                        </form>
                                        <a href="?action=delete&id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus pembayaran ini?')" class="btn btn-sm btn-danger">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data pembayaran</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
