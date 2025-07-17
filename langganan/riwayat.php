<?php
if (session_status() == PHP_SESSION_NONE) session_start();
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
</body>
</html>
