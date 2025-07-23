<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../auth/login.php");
    exit;
}
include_once __DIR__ . '/../config/database.php';
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BimbelAja - Siswa</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css"> <!-- kalau kamu punya custom CSS -->
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="dashboard.php">BimbelAja</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSiswa">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSiswa">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="materi.php">Materi</a></li>
        <li class="nav-item"><a class="nav-link" href="soal.php">Soal</a></li>
        <li class="nav-item"><a class="nav-link" href="langganan/riwayat.php">Langganan</a></li>
        <li class="nav-item"><a class="nav-link" href="forum.php">Forum</a></li>
        <li class="nav-item"><a class="nav-link" href="profil.php">Profil</a></li>
      </ul>
      <span class="navbar-text text-white">
        Halo, <?= htmlspecialchars($user['username']) ?> |
        <a href="../auth/logout.php" class="text-white text-decoration-underline">Logout</a>
      </span>
    </div>
  </div>
</nav>
<div class="container mt-4">
