<?php
include '../config/database.php';
include '../includes/auth.php';

if ($_SESSION['user']['role'] !== 'kasir') {
  header("Location: ../index.php");
  exit;
}

// Verifikasi & tolak transaksi
if (isset($_GET['verifikasi'])) {
  $id = intval($_GET['verifikasi']);
  mysqli_query($conn, "UPDATE pembayaran SET status='lunas' WHERE id=$id");
}
if (isset($_GET['tolak'])) {
  $id = intval($_GET['tolak']);
  mysqli_query($conn, "UPDATE pembayaran SET status='ditolak' WHERE id=$id");
}

// Ambil data transaksi
$result = mysqli_query($conn, "
  SELECT p.*, u.nama AS nama_siswa 
  FROM pembayaran p 
  JOIN users u ON p.user_id = u.id 
  ORDER BY p.tanggal DESC
");
?>

<?php include '../includes/header.php'; ?>

<div class="container my-5">
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Riwayat Transaksi</h5>
    </div>
    <div class="card-body">

      <?php if (isset($_GET['berhasil'])): ?>
        <div class="alert alert-success">Transaksi berhasil ditambahkan.</div>
      <?php endif; ?>

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>Nama Siswa</th>
              <th>Paket</th>
              <th>Harga</th>
              <th>Metode</th>
              <th>Status</th>
              <th>Bukti</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                <td><?= htmlspecialchars($row['paket'] ?? '-') ?></td>
                <td>Rp<?= number_format($row['harga'] ?? 0) ?></td>
                <td>
                  <?php 
                    $metode = $row['metode'] ?? '-';
                    echo '<span class="badge bg-info text-dark">' . htmlspecialchars(ucfirst($metode)) . '</span>';
                  ?>
                </td>
                <td>
                  <?php
                    $status = $row['status'] ?? '-';
                    $badgeClass = match($status) {
                      'lunas' => 'success',
                      'pending' => 'warning text-dark',
                      'ditolak' => 'danger',
                      default => 'secondary'
                    };
                  ?>
                  <span class="badge bg-<?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
                </td>
                <td>
                  <?php if (!empty($row['bukti_transfer'])): ?>
                    <a href="../uploads/<?= htmlspecialchars($row['bukti_transfer']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">Lihat</a>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['tanggal']) ?></td>
                <td>
                  <?php if (($row['status'] ?? '') === 'pending'): ?>
                    <a href="?verifikasi=<?= $row['id'] ?>" class="btn btn-sm btn-success me-1">✔</a>
                    <a href="?tolak=<?= $row['id'] ?>" class="btn btn-sm btn-danger">✖</a>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
