<?php
include '../config/database.php';
include '../includes/auth.php';

if ($_SESSION['user']['role'] !== 'kasir') {
  header("Location: ../index.php");
  exit;
}

$siswa_result = mysqli_query($conn, "SELECT * FROM users WHERE role='siswa'");
?>

<?php include '../includes/header.php'; ?>

<div class="container py-5">
  <h2 class="mb-4 text-center fw-bold">
    <i class="bi bi-people me-2"></i>Data Siswa
  </h2>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle text-center">
      <thead class="table-dark">
        <tr>
          <th>No</th>
          <th>Nama</th>
          <th>Username</th>
          <th>Kelas</th>
          <th>Jenjang</th>
          <th>Total Transaksi</th>
          <th>Status Terakhir</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $no = 1;
        while ($s = mysqli_fetch_assoc($siswa_result)) {
          $user_id = $s['id'];

          // Hitung total transaksi
          $total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran WHERE user_id = $user_id");
          $total = mysqli_fetch_assoc($total_result)['total'] ?? 0;

          // Ambil status terakhir
          $last_result = mysqli_query($conn, "SELECT status FROM pembayaran WHERE user_id = $user_id ORDER BY tanggal DESC LIMIT 1");
          $last_status = mysqli_fetch_assoc($last_result)['status'] ?? '-';
        ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($s['nama']) ?></td>
          <td><?= htmlspecialchars($s['username']) ?></td>
          <td><?= htmlspecialchars($s['kelas']) ?></td>
          <td><?= htmlspecialchars($s['jenjang']) ?></td>
          <td><?= $total ?></td>
          <td>
            <?php if ($last_status === 'berhasil'): ?>
              <span class="badge bg-success">Berhasil</span>
            <?php elseif ($last_status === 'pending'): ?>
              <span class="badge bg-warning text-dark">Pending</span>
            <?php elseif ($last_status === 'gagal'): ?>
              <span class="badge bg-danger">Gagal</span>
            <?php else: ?>
              <span class="badge bg-secondary">-</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
