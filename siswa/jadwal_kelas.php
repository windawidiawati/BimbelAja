<?php
session_start();
include '../config/database.php';

// Cek login dan role siswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../index.php");
    exit;
}

$siswa_id = $_SESSION['user']['id'];
$today = date('Y-m-d');

// Ambil langganan aktif siswa
$queryLangganan = mysqli_query($conn, "
    SELECT * FROM langganan 
    WHERE user_id = $siswa_id 
      AND status = 'aktif' 
    ORDER BY tanggal_mulai DESC 
    LIMIT 1
");

if (!$queryLangganan || mysqli_num_rows($queryLangganan) === 0) {
    die("Kamu belum memiliki langganan aktif.");
}

$langganan = mysqli_fetch_assoc($queryLangganan);
$kelas_id = isset($langganan['kelas_id']) ? (int)$langganan['kelas_id'] : 0;
$paket_id = isset($langganan['paket_id']) ? (int)$langganan['paket_id'] : 0;

// Validasi nilai kelas_id dan paket_id
if ($kelas_id === 0 || $paket_id === 0) {
    die("Data kelas atau paket langganan tidak valid.");
}

// Ambil jadwal berdasarkan kelas, paket, dan tanggal
$queryJadwal = "
    SELECT j.id, j.tanggal, j.jam_mulai, j.jam_selesai, 
           k.nama_kelas, kat.nama_kategori, u.nama 
    FROM jadwal_offline j
    JOIN kelas k ON j.kelas_id = k.id
    JOIN kategori_materi kat ON j.kategori_id = kat.id
    JOIN users u ON j.tutor_id = u.id AND u.role = 'tutor'
    WHERE j.kelas_id = $kelas_id
      AND j.paket_id = $paket_id
      AND j.tanggal >= '$today'
    ORDER BY j.tanggal ASC, j.jam_mulai ASC
";

$result = mysqli_query($conn, $queryJadwal);

if (!$result) {
    die("Terjadi kesalahan saat mengambil data jadwal: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Jadwal Kelas Kamu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h3>Jadwal Kelas Sesuai Paket</h3>

  <?php if (mysqli_num_rows($result) > 0): ?>
    <ul class="list-group mt-3">
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <li class="list-group-item">
          <strong><?= date('l, d M Y', strtotime($row['tanggal'])) ?></strong><br>
          Pukul: <?= htmlspecialchars($row['jam_mulai']) ?> - <?= htmlspecialchars($row['jam_selesai']) ?><br>
          Kelas: <?= htmlspecialchars($row['nama_kelas']) ?><br>
          Kategori: <?= htmlspecialchars($row['nama_kategori']) ?><br>
          Tutor: <?= htmlspecialchars($row['nama']) ?>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php else: ?>
    <div class="alert alert-info mt-3">
      Belum ada jadwal terdekat untuk kelas dan paket kamu.
    </div>
  <?php endif; ?>
</div>
</body>
</html>
