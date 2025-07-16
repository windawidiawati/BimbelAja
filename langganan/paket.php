<<<<<<< HEAD
=======
<?php
if (session_status() == PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php");
    exit;
}

include_once __DIR__ . '/../config/database.php';

$jenjang = $_SESSION['user']['jenjang'] ?? '';
$kelas = $_SESSION['user']['kelas'] ?? '';

$query = mysqli_prepare($conn, "SELECT * FROM paket WHERE jenjang = ? AND kelas = ? AND status = 'aktif'");
mysqli_stmt_bind_param($query, "ss", $jenjang, $kelas);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Paket Untuk Anda | BimbelAja</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }
    .card {
      border: none;
      border-radius: 16px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
      transition: transform 0.2s ease;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card-title {
      font-size: 1.25rem;
      font-weight: bold;
      color: #0d6efd;
    }
    .btn-primary {
      border-radius: 30px;
      padding: 8px 20px;
    }
    .badge-category {
      font-size: 0.8rem;
      padding: 4px 8px;
      background-color: #0d6efd;
      color: white;
      border-radius: 12px;
    }
  </style>
</head>
<body>
<div class="container py-5">
  <h2 class="mb-4 text-center">Paket Rekomendasi Untuk Anda</h2>
  <p class="text-center mb-5">Jenjang: <strong><?= htmlspecialchars($jenjang) ?></strong> &bull; Kelas: <strong><?= htmlspecialchars($kelas) ?></strong></p>

  <div class="row">
    <?php if (mysqli_num_rows($result) > 0): ?>
      <?php while ($paket = mysqli_fetch_assoc($result)): ?>
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <span class="badge-category mb-2"><?= htmlspecialchars($paket['kategori']) ?></span>
              <h5 class="card-title"><?= htmlspecialchars($paket['nama']) ?></h5>
              <p class="card-text text-muted" style="min-height: 60px;">
                <?= nl2br(htmlspecialchars(substr($paket['deskripsi'], 0, 100))) ?>...
              </p>
              <ul class="list-unstyled small text-muted mb-3">
                <li><strong>Harga:</strong> Rp <?= number_format($paket['harga'], 0, ',', '.') ?></li>
                <li><strong>Durasi:</strong> <?= $paket['durasi'] . ' ' . htmlspecialchars($paket['satuan_durasi']) ?></li>
              </ul>
              <a href="checkout.php?paket_id=<?= $paket['id'] ?>" class="btn btn-primary w-100">Langganan Sekarang</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="col-12 text-center">
        <div class="alert alert-warning">Belum ada paket aktif untuk jenjang dan kelas Anda.</div>
      </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
>>>>>>> origin
