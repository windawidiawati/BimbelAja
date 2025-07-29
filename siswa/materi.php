<?php 
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'siswa') {
  header('Location: ../index.php'); exit;

}
include '../includes/siswa_header_langganan.php';
include '../config/database.php';

$user_id = $_SESSION['user']['id'];
$jenjang = $_SESSION['user']['jenjang'];

$qPaket = mysqli_query($conn, "SELECT * FROM langganan WHERE user_id = $user_id AND status = 'aktif' ORDER BY id DESC LIMIT 1");
$dataPaket = mysqli_fetch_assoc($qPaket);
$paket_siswa = $dataPaket['paket'] ?? null;

if (!$paket_siswa) {
  echo '<div class="container mt-5"><div class="alert alert-warning">Kamu belum memiliki langganan aktif.</div></div>';
  include '../includes/footer.php';
  exit;
}

$sqlKelas = "SELECT id FROM kelas WHERE jenjang = '$jenjang'";
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

$sqlMateri = "
  SELECT m.*, k.nama_kelas, kat.nama_kategori 
  FROM materi m
  JOIN kelas k ON m.kelas_id = k.id
  LEFT JOIN kategori_materi kat ON m.kategori_id = kat.id
  WHERE m.kelas_id IN ($idListStr)
  AND m.status = 'diterima'
  ORDER BY m.id DESC
";

$resMateri = $conn->query($sqlMateri);

if (isset($_GET['view']) && isset($_GET['type'])) {
  $file = basename($_GET['view']);
  $filePath = "../assets/uploads/" . $file;

  if (file_exists($filePath)) {
    header("Location: $filePath");
    exit;
  } else {
    echo '<div class="alert alert-danger mt-4">File tidak ditemukan.</div>';
    exit;
  }
}
?>

<style>
  body {
    background: #f8f9fa;
  }

  h3 {
    font-weight: 700;
    font-size: 1.8rem;
    color: #495057;
    margin-bottom: 2rem;
    text-align: center;
    position: relative;
  

  }

  h3::after {
    content: '';
    display: block;
    width: 60px;
    height: 4px;
    margin: 10px auto 0;
    border-radius: 2px;
  }

  .card {
    border-radius: 18px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.07);
    transition: 0.3s ease;
    border: none;
    background: #fff;
    position: relative;
    overflow: hidden;
  }

  .card:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
  }

  .card-title {
    font-weight: 600;
    font-size: 1.2rem;
    color: #0d6efd;
  }

  .card-text {
    font-size: 0.95rem;
    color: #495057;
  }

  .btn-sm {
    font-size: 0.85rem;
    border-radius: 8px;
    margin-top: 5px;
    transition: 0.2s;
  }

  .btn-sm:hover {
    opacity: 0.9;
    transform: scale(1.03);
  }

  .badge.bg-info {
    background-color: #00bcd4 !important;
    font-size: 0.85rem;
    padding: 6px 10px;
    border-radius: 50px;
    font-weight: 500;
  }

  .card-footer {
    background: #f1f3f5;
    border-top: none;
    padding: 12px 16px;
  }

  .text-muted {
    font-size: 0.8rem;
    color: #6c757d;
  }

  /* Tambahan aksen pelangi di sisi kartu */
  .card::before {
    content: '';
    position: absolute;
    top: -20px;
    right: -20px;
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    opacity: 0.1;
    transform: rotate(45deg);
    border-radius: 20px;
  }
</style>


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
            <div class="card-footer d-flex justify-content-between">
             <?php if ($row['tipe_file'] === 'video'): ?>
               <a href="materi.php?view=<?= urlencode($row['file']) ?>&type=video" class="btn btn-primary btn-sm" target="_blank">Tonton</a>
               <?php else: ?>
                <a href="materi.php?view=<?= urlencode($row['file']) ?>&type=pdf" class="btn btn-warning btn-sm" target="_blank">Lihat</a>
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

<script>
  function openViewer(type, file) {
    const modalContent = document.getElementById('modalContent');
    const filePath = '/BimbelAja/assets/uploads/' + file;

    let content = '';
    if (type === 'video') {
      content = `<video width="100%" height="auto" controls><source src="${filePath}" type="video/mp4">Browser kamu tidak mendukung video.</video>`;
    } else {
      content = `<iframe src="${filePath}" width="100%" height="500px" frameborder="0"></iframe>`;
    }
    modalContent.innerHTML = content;
    var myModal = new bootstrap.Modal(document.getElementById('materiModal'));
    myModal.show();
  }
</script>

<?php include '../includes/footer.php'; ?>
