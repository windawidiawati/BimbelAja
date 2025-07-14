<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'siswa') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
include '../config/database.php';

$user_id = $_SESSION['user']['id'];
$jenjang = $_SESSION['user']['jenjang'];

// Ambil daftar id kelas yang sesuai jenjang siswa
$sqlKelas = "SELECT id FROM kelas WHERE nama_kelas LIKE '%$jenjang%'";
$resKelas = $conn->query($sqlKelas);

$kelasIdList = [];
while ($row = $resKelas->fetch_assoc()) {
  $kelasIdList[] = $row['id'];
}

// Jika tidak ada kelas cocok, berhenti
if (count($kelasIdList) === 0) {
  echo '<div class="container mt-5">';
  echo '<div class="alert alert-warning">Tidak ditemukan materi untuk jenjang: ' . htmlspecialchars($jenjang) . '</div>';
  echo '</div>';
  include '../includes/footer.php';
  exit;
}

// Ambil materi berdasarkan kelas siswa
$idListStr = implode(',', array_map('intval', $kelasIdList));
$sqlMateri = "
  SELECT m.*, k.nama_kategori, ks.nama_kelas
  FROM materi m
  LEFT JOIN kategori_materi k ON m.kategori_id = k.id
  LEFT JOIN kelas ks ON m.kelas_id = ks.id
  WHERE m.kelas_id IN ($idListStr) AND m.status = 'diterima'
";
$resMateri = $conn->query($sqlMateri);
?>

<div class="container mt-5">
  <h3>Materi Sesuai Jenjang <?= htmlspecialchars($jenjang) ?></h3>
  <p>Materi berikut sesuai dengan jenjang dan kelas kamu:</p>

  <?php if ($resMateri->num_rows > 0): ?>
    <div class="row">
      <?php while ($row = $resMateri->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($row['judul']) ?></h5>
              <p class="card-text"><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></p>
              <small class="text-muted">Kategori: <?= htmlspecialchars($row['nama_kategori']) ?><br>
              Kelas: <?= htmlspecialchars($row['nama_kelas']) ?></small>
            </div>
            <div class="card-footer">
              <?php if ($row['tipe_file'] === 'video'): ?>
                <a href="../uploads/<?= $row['file'] ?>" class="btn btn-primary" target="_blank">Tonton Video</a>
              <?php else: ?>
                <a href="../uploads/<?= $row['file'] ?>" class="btn btn-success" download>Download PDF</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-info">Belum ada materi tersedia untuk jenjang kamu.</div>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
