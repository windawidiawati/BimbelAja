<?php
session_start();
include '../config/database.php';
include '../includes/admin_header.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Data Summary
$jumlah_admin     = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role = 'admin'"));
$jumlah_tutor     = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role = 'tutor'"));
$jumlah_siswa     = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role = 'siswa'"));
$jumlah_kasir     = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role = 'kasir'"));
$jumlah_paket     = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM paket"));
$jumlah_langganan = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM langganan"));

$pemasukan_query = mysqli_query($conn, "
    SELECT SUM(p.harga) AS total
    FROM langganan l
    JOIN paket p ON l.paket = p.nama
");
$pemasukan = mysqli_fetch_assoc($pemasukan_query);
$total_pemasukan = $pemasukan['total'] ?? 0;
?>

<div class="content p-4">
  <h4 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2"></i>Dashboard Admin</h4>

  <div class="row g-4">
    <!-- User Summary -->
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100 bg-light">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="me-3 text-primary fs-2"><i class="bi bi-person-gear"></i></div>
            <div>
              <div class="text-muted">Total Admin</div>
              <div class="fw-bold fs-5"><?= $jumlah_admin ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100 bg-light">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="me-3 text-success fs-2"><i class="bi bi-person-check"></i></div>
            <div>
              <div class="text-muted">Total Tutor</div>
              <div class="fw-bold fs-5"><?= $jumlah_tutor ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100 bg-light">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="me-3 text-info fs-2"><i class="bi bi-person-lines-fill"></i></div>
            <div>
              <div class="text-muted">Total Siswa</div>
              <div class="fw-bold fs-5"><?= $jumlah_siswa ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100 bg-light">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="me-3 text-warning fs-2"><i class="bi bi-person-badge-fill"></i></div>
            <div>
              <div class="text-muted">Total Kasir</div>
              <div class="fw-bold fs-5"><?= $jumlah_kasir ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Paket dan Langganan -->
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100 bg-primary text-white">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="me-3 fs-2"><i class="bi bi-box-seam"></i></div>
            <div>
              <div>Total Paket</div>
              <div class="fw-bold fs-5"><?= $jumlah_paket ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100 bg-success text-white">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="me-3 fs-2"><i class="bi bi-bag-check-fill"></i></div>
            <div>
              <div>Total Langganan</div>
              <div class="fw-bold fs-5"><?= $jumlah_langganan ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100 bg-warning text-dark">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="me-3 fs-2"><i class="bi bi-cash-stack"></i></div>
            <div>
              <div>Total Pemasukan</div>
              <div class="fw-bold fs-5">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</nav>

<!-- Sidebar -->
<div class="sidebar">
  <h5 class="text-white text-center">Admin Panel</h5>
  <ul class="nav flex-column">
    <li class="nav-item">
      <a class="nav-link <?= ($current_page === 'dashboard.php') ? 'active' : '' ?>" href="dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($current_page === 'kelola_materi.php') ? 'active' : '' ?>" href="kelola_materi.php">
        <i class="bi bi-file-earmark-text"></i> Kelola Materi
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($current_page === 'kelola_user.php') ? 'active' : '' ?>" href="kelola_user.php">
        <i class="bi bi-person"></i> Kelola User
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($current_page === 'statistik.php') ? 'active' : '' ?>" href="statistik.php">
        <i class="bi bi-bar-chart"></i> Statistik
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($current_page === 'verifikasi_pembayaran.php') ? 'active' : '' ?>" href="verifikasi_pembayaran.php">
        <i class="bi bi-credit-card"></i> Verifikasi Pembayaran
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($current_page === 'chekout.php') ? 'active' : '' ?>" href="/BimbelAja/langganan/paket.php">
        <i class="bi bi-people me-1"></i>Pelanggan
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($current_page === 'chekout.php') ? 'active' : '' ?>" href="kelola_paket.php">
        <i class="bi bi-people me-1"></i>Kelola Paket
      </a>
    </li>
  </ul>

</div>

<!-- Konten -->


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

</div>

<?php include '../includes/admin_footer.php'; ?>

