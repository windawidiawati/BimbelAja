<?php
include '../includes/auth.php';
include '../config/database.php';

// Cek role kasir
if ($_SESSION['user']['role'] !== 'kasir') {
    header('Location: ../index.php');
    exit;
}

// Ambil data summary
$total_transaksi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pembayaran"))['total'] ?? 0;
$total_online = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pembayaran WHERE metode='transfer'"))['total'] ?? 0;
$total_tunai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pembayaran WHERE metode='tunai'"))['total'] ?? 0;
$total_siswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='siswa'"))['total'] ?? 0;

include '../includes/header.php';
?>

<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f8f9fa;
    }
    .sidebar {
        width: 240px;
        background-color: #0d6efd;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        padding-top: 20px;
    }
    .sidebar a {
        color: white;
        display: block;
        padding: 12px 20px;
        text-decoration: none;
        font-size: 16px;
    }
    .sidebar a:hover {
        background-color: #0b5ed7;
    }
    .sidebar .logo {
        text-align: center;
        margin-bottom: 30px;
        color: white;
        font-size: 20px;
        font-weight: bold;
    }
    .content {
        margin-left: 240px;
        padding: 20px;
    }
    .card-icon {
        font-size: 2rem;
    }
</style>

<div class="sidebar">
    <div class="logo">
        <i class="bi bi-cash-coin me-2"></i>BimbelAja
    </div>
    <a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="checkout_tunai.php"><i class="bi bi-cash-stack me-2"></i>Checkout Tunai</a>
    <a href="data_siswa.php"><i class="bi bi-people me-2"></i>Data Siswa</a>
    <a href="transaksi.php"><i class="bi bi-receipt-cutoff me-2"></i>Transaksi</a>
    <a href="profil.php"><i class="bi bi-person-circle me-2"></i>Profil</a>
</div>

<div class="content">
    <h4 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2"></i>Dashboard Kasir</h4>
    <p>Selamat datang, <b><?= $_SESSION['user']['username']; ?></b>! Semangat bekerja hari ini 😊</p>

    <div class="row g-4 mt-3">
        <!-- Total Transaksi -->
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="text-primary card-icon me-3"><i class="bi bi-receipt"></i></div>
                    <div>
                        <div class="text-muted small">Total Transaksi</div>
                        <div class="fw-bold fs-5"><?= $total_transaksi ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaksi Online -->
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="text-success card-icon me-3"><i class="bi bi-credit-card"></i></div>
                    <div>
                        <div class="text-muted small">Transaksi Online</div>
                        <div class="fw-bold fs-5"><?= $total_online ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaksi Tunai -->
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="text-warning card-icon me-3"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <div class="text-muted small">Transaksi Tunai</div>
                        <div class="fw-bold fs-5"><?= $total_tunai ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Siswa -->
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="text-info card-icon me-3"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="text-muted small">Total Siswa</div>
                        <div class="fw-bold fs-5"><?= $total_siswa ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
