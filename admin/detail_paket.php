<?php
include '../includes/auth.php';
include '../includes/admin_header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'admin') {
  header('Location: ../index.php'); exit;
}

if (!isset($_GET['id'])) {
  echo "<div class='alert alert-danger'>ID tidak ditemukan.</div>";
  include '../includes/admin_footer.php';
  exit;
}

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM paket WHERE id = $id");
if (!$result || mysqli_num_rows($result) == 0) {
  echo "<div class='alert alert-warning'>Data tidak ditemukan.</div>";
  include '../includes/admin_footer.php';
  exit;
}
$data = mysqli_fetch_assoc($result);
?>

<div class="content">
  <h3>Detail Paket: <?= htmlspecialchars($data['nama']) ?></h3>
  <a href="kelola_paket.php" class="btn btn-secondary mb-3">← Kembali</a>

  <table class="table table-bordered">
    <tr><th>Nama Paket</th><td><?= htmlspecialchars($data['nama']) ?></td></tr>
    <tr><th>Kategori</th><td><?= htmlspecialchars($data['kategori']) ?></td></tr>
    <tr><th>Tipe</th><td><?= htmlspecialchars($data['tipe']) ?></td></tr>
    <tr><th>Jenjang</th><td><?= htmlspecialchars($data['jenjang']) ?></td></tr>
    <tr><th>Kelas</th><td><?= htmlspecialchars($data['kelas']) ?></td></tr>
    <tr><th>Harga</th><td>Rp <?= number_format($data['harga'], 0, ',', '.') ?></td></tr>
    <tr><th>Durasi</th><td><?= htmlspecialchars($data['durasi'] . ' ' . $data['satuan_durasi']) ?></td></tr>
    <tr><th>Deskripsi</th><td><?= nl2br(htmlspecialchars($data['deskripsi'])) ?></td></tr>
    <tr><th>Status</th><td><?= htmlspecialchars($data['status']) ?></td></tr>
    <tr><th>Dibuat</th><td><?= $data['created_at'] ?></td></tr>
    <tr><th>Diperbarui</th><td><?= $data['updated_at'] ?></td></tr>
  </table>
</div>

<?php include '../includes/admin_footer.php'; ?>
