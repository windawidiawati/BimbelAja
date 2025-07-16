<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php");
    exit;
}

include_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user']['id'];
$query = mysqli_query($conn, "SELECT * FROM pembayaran WHERE user_id = $user_id ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Pembayaran | BimbelAja</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h2>Riwayat Pembayaran</h2>
  <table class="table table-bordered mt-4">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Nama Paket</th>
        <th>Harga</th>
        <th>Status</th>
        <th>Tanggal</th>
      </tr>
    </thead>
    <tbody>
      <?php if (mysqli_num_rows($query) > 0): $no = 1; ?>
        <?php while ($row = mysqli_fetch_assoc($query)): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['paket']) ?></td>
            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
            <td>
              <span class="badge bg-<?= 
                $row['status'] === 'lunas' ? 'success' : 
                ($row['status'] === 'pending' ? 'warning' : 'danger')
              ?>">
                <?= ucfirst($row['status']) ?>
              </span>
            </td>
            <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center">Belum ada riwayat pembayaran.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
