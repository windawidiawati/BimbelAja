<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Ambil data langganan
$query_langganan = "SELECT * FROM langganan WHERE user_id = $user_id ORDER BY created_at DESC";
$result_langganan = mysqli_query($conn, $query_langganan);

// Ambil data pembayaran
$query_pembayaran = "SELECT * FROM pembayaran WHERE user_id = $user_id ORDER BY tanggal DESC";
$result_pembayaran = mysqli_query($conn, $query_pembayaran);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Langganan & Pembayaran | BimbelAja</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h2 class="mb-4">Riwayat Langganan</h2>
  <table class="table table-bordered">
    <thead class="table-primary">
      <tr>
        <th>ID</th>
        <th>Paket</th>
        <th>Jenjang</th>
        <th>Kelas</th>
        <th>Tanggal Mulai</th>
        <th>Tanggal Berakhir</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result_langganan && mysqli_num_rows($result_langganan) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result_langganan)): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['paket']) ?></td>
            <td><?= $row['jenjang'] ?></td>
            <td><?= $row['kelas'] ?></td>
            <td><?= $row['tanggal_mulai'] ?></td>
            <td><?= $row['tanggal_berakhir'] ?></td>
            <td><?= ucfirst($row['status']) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="7" class="text-center">Belum ada data langganan.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <h2 class="mb-4 mt-5">Riwayat Pembayaran</h2>
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
      <?php if ($result_pembayaran && mysqli_num_rows($result_pembayaran) > 0): $no = 1; ?>
        <?php while ($row = mysqli_fetch_assoc($result_pembayaran)): ?>
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
