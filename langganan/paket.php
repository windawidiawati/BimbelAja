<?php
if (session_status() == PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php");
    exit;
}

include_once __DIR__ . '/../config/database.php';
include_once '../includes/header.php';

$jenjang = $_SESSION['user']['jenjang'] ?? '';
$kelas = $_SESSION['user']['kelas'] ?? '';

$query = mysqli_prepare($conn, "SELECT * FROM paket WHERE jenjang = ? AND kelas = ? AND status = 'aktif'");
mysqli_stmt_bind_param($query, "ss", $jenjang, $kelas);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);
?>

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

<div class="container my-5">
  <h2 class="section-title">🎓 Paket Rekomendasi Untuk Anda</h2>
  <p class="info-box">Jenjang: <strong><?= htmlspecialchars($jenjang) ?></strong> &bull; Kelas: <strong><?= htmlspecialchars($kelas) ?></strong></p>

  <div class="row g-4">
    <?php if (mysqli_num_rows($result) > 0): ?>
      <?php while ($paket = mysqli_fetch_assoc($result)): ?>
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
      <div class="col-12">
        <div class="alert alert-warning text-center">
          <i class="fas fa-exclamation-circle me-2"></i> Belum ada paket aktif untuk jenjang dan kelas Anda.
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
