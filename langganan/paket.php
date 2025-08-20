<?php
if (session_status() == PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php");
    exit;
}

include_once __DIR__ . '/../config/database.php';
include_once '../includes/header.php';

$search = trim($_GET['search'] ?? '');
$jenjang = $_SESSION['user']['jenjang'] ?? '';
$kelas = $_SESSION['user']['kelas'] ?? '';

// Tangkap keyword pencarian (jika ada)
$search = $_GET['search'] ?? '';
$paket_search = [];
$paket_rekomendasi = [];
$semua_paket = [];


// Rekomendasi berdasarkan jenjang & kelas
$query_rekom = mysqli_prepare($conn, "SELECT * FROM paket WHERE jenjang = ? AND kelas = ? AND status = 'aktif'");
mysqli_stmt_bind_param($query_rekom, "ss", $jenjang, $kelas);
mysqli_stmt_execute($query_rekom);
$result_rekom = mysqli_stmt_get_result($query_rekom);

// Semua paket (filtered jika ada search)
if (!empty($search)) {
    $query_all = mysqli_prepare($conn, "SELECT * FROM paket WHERE status = 'aktif' AND nama LIKE ?");
    $like_search = '%' . $search . '%';
    mysqli_stmt_bind_param($query_all, "s", $like_search);
} else {
    $query_all = mysqli_prepare($conn, "SELECT * FROM paket WHERE status = 'aktif'");
}
mysqli_stmt_execute($query_all);
$result_all = mysqli_stmt_get_result($query_all);
?>

<!-- STYLE -->
<style>
  body {
    background: linear-gradient(to bottom, #e3f2fd, #ffffff);
    font-family: 'Segoe UI', sans-serif;
  }

  .section-title {
    text-align: center;
    color: #0d47a1;
    margin-top: 40px;
  }

  .info-box {
    text-align: center;
    font-size: 1.1rem;
    margin-bottom: 40px;
    color: #555;
  }

  .card-paket {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
    border: none;
  }

  .card-paket:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
  }

  .ribbon {
    position: absolute;
    top: 15px;
    right: -30px;
    background: #ff7043;
    color: white;
    padding: 5px 45px;
    transform: rotate(45deg);
    font-size: 0.75rem;
    font-weight: bold;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  }

  .card-body {
    padding: 25px;
  }

  .card-title {
    font-weight: 600;
    font-size: 1.2rem;
    color: #1565c0;
    min-height: 50px;
  }

  .card-text {
    color: #555;
    font-size: 0.95rem;
    margin-top: 10px;
    margin-bottom: 15px;
    min-height: 60px;
  }

  .btn-langganan {
    background: linear-gradient(to right, #42a5f5, #1e88e5);
    border: none;
    border-radius: 30px;
    font-weight: bold;
    transition: 0.2s;
    color: white;
  }

  .btn-langganan:hover {
    background: linear-gradient(to right, #1e88e5, #1565c0);
  }

  .icon-info {
    color: #0d47a1;
    margin-right: 6px;
  }

  .alert-warning {
    background-color: #fff3cd;
    color: #856404;
    border-radius: 10px;
  }
</style>

<!-- KONTEN -->
<div class="container my-5">
   <!-- Search Bar -->
  <form method="get" class="mb-4">
    <div class="input-group">
      <input type="text" name="search" class="form-control" placeholder="Cari nama paket..." value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-primary" type="submit">Cari</button>
    </div>
  </form>
  <h2 class="section-title">🎓 Paket Rekomendasi Untuk Anda</h2>
  <p class="info-box">Jenjang: <strong><?= htmlspecialchars($jenjang) ?></strong> &bull; Kelas: <strong><?= htmlspecialchars($kelas) ?></strong></p>
  <div class="row mt-4">
  <?php if (!empty($search) && mysqli_num_rows($result_all) > 0): ?>
    <div class="col-12 mb-3">
      <h5>Hasil Pencarian untuk: "<?= htmlspecialchars($search) ?>"</h5>
    </div>
    <?php while ($paket = mysqli_fetch_assoc($result_all)): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card card-paket h-100 position-relative">
          <div class="ribbon"><?= htmlspecialchars($paket['kategori']) ?></div>
          <div class="card-body d-flex flex-column justify-content-between">
            <div>
              <h5 class="card-title"><?= htmlspecialchars($paket['nama']) ?></h5>
              <p class="card-text"><?= nl2br(htmlspecialchars(substr($paket['deskripsi'], 0, 100))) ?>...</p>
            </div>
            <ul class="list-unstyled text-muted small">
              <li><i class="fas fa-tags icon-info"></i><strong>Harga:</strong> Rp <?= number_format($paket['harga'], 0, ',', '.') ?></li>
              <li><i class="fas fa-clock icon-info"></i><strong>Durasi:</strong> <?= $paket['durasi'] . ' ' . htmlspecialchars($paket['satuan_durasi']) ?></li>
            </ul>
            <a href="checkout.php?paket_id=<?= $paket['id'] ?>" class="btn btn-langganan w-100 mt-3">Langganan Sekarang</a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  
  <?php else: ?>
    <!-- TAMPILKAN PAKET REKOMENDASI -->
    <?php if (mysqli_num_rows($result_rekom) > 0): ?>
      <div class="col-12 mb-3">
        <h5>Paket Rekomendasi untuk kamu</h5>
      </div>
      <?php mysqli_data_seek($result_rekom, 0); ?>
      <?php while ($paket = mysqli_fetch_assoc($result_rekom)): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card card-paket h-100 position-relative">
            <div class="ribbon"><?= htmlspecialchars($paket['kategori']) ?></div>
            <div class="card-body d-flex flex-column justify-content-between">
              <div>
                <h5 class="card-title"><?= htmlspecialchars($paket['nama']) ?></h5>
                <p class="card-text"><?= nl2br(htmlspecialchars(substr($paket['deskripsi'], 0, 100))) ?>...</p>
              </div>
              <ul class="list-unstyled text-muted small">
                <li><i class="fas fa-tags icon-info"></i><strong>Harga:</strong> Rp <?= number_format($paket['harga'], 0, ',', '.') ?></li>
                <li><i class="fas fa-clock icon-info"></i><strong>Durasi:</strong> <?= $paket['durasi'] . ' ' . htmlspecialchars($paket['satuan_durasi']) ?></li>
              </ul>
              <a href="checkout.php?paket_id=<?= $paket['id'] ?>" class="btn btn-langganan w-100 mt-3">Langganan Sekarang</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
<<<<<<< HEAD
  </div>
</div>
</body>
</html>

=======

    <!-- SEMUA PAKET -->
    <?php mysqli_data_seek($result_all, 0); ?>
    <div class="col-12 mt-4 mb-3">
      <h5>Semua Paket</h5>
    </div>
    <?php while ($paket = mysqli_fetch_assoc($result_all)): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card card-paket h-100 position-relative">
          <div class="ribbon"><?= htmlspecialchars($paket['kategori']) ?></div>
          <div class="card-body d-flex flex-column justify-content-between">
            <div>
              <h5 class="card-title"><?= htmlspecialchars($paket['nama']) ?></h5>
              <p class="card-text"><?= nl2br(htmlspecialchars(substr($paket['deskripsi'], 0, 100))) ?>...</p>
            </div>
            <ul class="list-unstyled text-muted small">
              <li><i class="fas fa-tags icon-info"></i><strong>Harga:</strong> Rp <?= number_format($paket['harga'], 0, ',', '.') ?></li>
              <li><i class="fas fa-clock icon-info"></i><strong>Durasi:</strong> <?= $paket['durasi'] . ' ' . htmlspecialchars($paket['satuan_durasi']) ?></li>
            </ul>
            <a href="checkout.php?paket_id=<?= $paket['id'] ?>" class="btn btn-langganan w-100 mt-3">Langganan Sekarang</a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div
<?php include '../includes/footer.php'; ?>
>>>>>>> c093bc66fee073282575d2ed979fa6e3bd10d5f8
