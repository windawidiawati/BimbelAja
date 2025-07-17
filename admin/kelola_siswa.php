<?php
session_start();
include '../config/database.php';
include '../includes/admin_header.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$id = $_GET['id'] ?? '';
if (!$id) {
    echo "ID siswa tidak ditemukan."; exit;
}

$siswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $id AND role = 'siswa'"));
if (!$siswa) {
    echo "Siswa tidak ditemukan."; exit;
}

// Riwayat pembayaran
$pembayaran = mysqli_query($conn, "SELECT * FROM pembayaran WHERE user_id = $id ORDER BY tanggal DESC");
?>

<div class="container mt-4">
  <h3>Detail Siswa: <?= htmlspecialchars($siswa['nama']) ?></h3>
  <p><strong>Username:</strong> <?= $siswa['username'] ?></p>
  <p><strong>Jenjang:</strong> <?= $siswa['jenjang'] ?> | <strong>Kelas:</strong> <?= $siswa['kelas'] ?></p>

  <h5 class="mt-4">Riwayat Pembayaran</h5>
  <table class="table table-bordered">
    <thead><tr><th>No</th><th>Paket</th><th>Harga</th><th>Status</th><th>Tanggal</th></tr></thead>
    <tbody>
      <?php $no = 1; while ($row = mysqli_fetch_assoc($pembayaran)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= $row['paket'] ?></td>
          <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
          <td><?= ucfirst($row['status']) ?></td>
          <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include '../includes/admin_footer.php'; ?>

