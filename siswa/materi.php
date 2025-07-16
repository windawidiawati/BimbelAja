<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'siswa') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
include '../config/database.php';

$user_id = $_SESSION['user']['id'];
$jenjang = $_SESSION['user']['jenjang'];

// Ambil paket langganan aktif siswa
$qPaket = mysqli_query($conn, "
  SELECT paket FROM langganan 
  WHERE user_id = $user_id AND status = 'aktif' 
  ORDER BY created_at DESC LIMIT 1
");
$dataPaket = mysqli_fetch_assoc($qPaket);
$paket_siswa = $dataPaket['paket'] ?? null;

if (!$paket_siswa) {
  echo '<div class="container mt-5"><div class="alert alert-warning">Kamu belum memiliki langganan aktif.</div></div>';
  include '../includes/footer.php';
  exit;
}

// Ambil daftar ID kelas berdasarkan jenjang siswa
$sqlKelas = "SELECT id FROM kelas WHERE nama_kelas LIKE '%$jenjang%'";
$resKelas = $conn->query($sqlKelas);
$kelasIdList = [];
while ($row = $resKelas->fetch_assoc()) {
  $kelasIdList[] = $row['id'];
}

if (count($kelasIdList) === 0) {
  echo '<div class="container mt-5"><div class="alert alert-warning">Tidak ditemukan materi untuk jenjang: ' . htmlspecialchars($jenjang) . '</div></div>';
  include '../includes/footer.php';
  exit;
}

$idListStr = implode(',', array_map('intval', $kelasIdList));

// Ambil materi yang sesuai jenjang + status disetujui + sesuai paket
$sqlMateri = "
  SELECT m.*, k.nama_kategori, ks.nama_kelas
  FROM materi m
  LEFT JOIN kategori_materi k ON m.kategori_id = k.id
  LEFT JOIN kelas ks ON m.kelas_id = ks.id
  WHERE m.kelas_id IN ($idListStr)
    AND m.status = 'diterima'
    AND m.paket = '$paket_siswa'
";
$resMateri = $conn->query($sqlMateri);
?>

<div class="container mt-5">
  <h3>Materi Paket <span class="badge bg-info"><?= htmlspecialchars(ucfirst($paket_siswa)) ?></span> - Jenjang <?= htmlspecialchars($jenjang) ?></h3>
  <p>Materi berikut tersedia sesuai dengan paket langganan kamu:</p>

  <?php if ($resMateri->num_rows > 0): ?>
    <div class="row">
      <?php while ($row = $resMateri->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($row['judul']) ?></h5>
              <p class="card-text"><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></p>
              <small class="text-muted">
                Kategori: <?= htmlspecialchars($row['nama_kategori']) ?><br>
                Kelas: <?= htmlspecialchars($row['nama_kelas']) ?>
              </small>
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
    <div class="alert alert-info">Belum ada materi untuk paket <b><?= htmlspecialchars($paket_siswa) ?></b> dan jenjang <b><?= htmlspecialchars($jenjang) ?></b>.</div>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
