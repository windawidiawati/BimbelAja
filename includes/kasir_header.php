<?php
// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek role kasir
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BimbelAja - Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
        }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }
        .sidebar {
            position: fixed; top: 56px; left: 0;
            width: 240px; height: 100%;
            background-color: #0d6efd;
            padding-top: 20px;
        }
        .sidebar a {
            color: white; padding: 12px 20px; display: block;
            text-decoration: none;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #0056b3;
        }
        .content {
            margin-left: 240px; padding: 20px;
        }
        @media(max-width: 768px) {
            .sidebar { display: none; }
            .content { margin-left: 0; }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="bi bi-cash-stack me-2"></i>BimbelAja Kasir
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link text-white <?= ($current_page === 'profil.php') ? 'active' : '' ?>" href="profil.php">
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

<!-- Sidebar -->
<div class="sidebar d-none d-lg-block">
    <a class="<?= ($current_page === 'dashboard.php') ? 'active' : '' ?>" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a class="<?= ($current_page === 'checkout_tunai.php') ? 'active' : '' ?>" href="checkout_tunai.php"><i class="bi bi-cash-coin me-2"></i>Checkout Tunai</a>
    <a class="<?= ($current_page === 'data_siswa.php') ? 'active' : '' ?>" href="data_siswa.php"><i class="bi bi-people me-2"></i>Data Siswa</a>
    <a class="<?= ($current_page === 'transaksi.php') ? 'active' : '' ?>" href="transaksi.php"><i class="bi bi-receipt-cutoff me-2"></i>Riwayat Transaksi</a>
</div>

<div class="content">
