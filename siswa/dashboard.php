<?php
include '../includes/auth.php';
$title = "Dashboard Siswa";
include '../includes/siswa_header_langganan.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'siswa') {
  header('Location: ../index.php'); exit;
}

$user_id = $_SESSION['user']['id'];
$username = $_SESSION['user']['nama'];
// Ambil data langganan aktif
$user_id = $_SESSION['user']['id'];
$today = date('Y-m-d');

$sql = "SELECT * FROM langganan WHERE user_id = $user_id AND status = 'aktif' ORDER BY tanggal_berakhir DESC LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($row = mysqli_fetch_assoc($result)) {
    if ($row['tanggal_berakhir'] < $today) {
        // Update jadi expired
        $update = "UPDATE langganan SET status = 'expired' WHERE id = " . $row['id'];
        mysqli_query($conn, $update);

        // Redirect ke halaman riwayat atau notifikasi
        header("Location: ../langganan/riwayat.php?expired=1");
        exit;
    }
} else {
    // Kalau tidak ada langganan aktif
    header("Location: ../langganan/riwayat.php?belum_langganan=1");
    exit;
}


// Ambil jenis paket langganan aktif siswa
$query = "SELECT paket FROM langganan WHERE user_id = $user_id AND status = 'aktif' ORDER BY created_at DESC LIMIT 1";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$paket = $row['paket'] ?? 'none';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard BimbelAja</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  </head>
  <body>
    <div class="container mt-5">
    <div class="text-center mb-4">
      <h2 class="fw-bold">Dashboard Siswa</h2>
      <p>Halo, <strong><?= htmlspecialchars($username); ?></strong>. Selamat belajar dan semangat terus!</p>
      <p class="badge bg-info text-dark">Paket Langganan: <strong><?= ucfirst($paket); ?></strong></p>
    </div>

    <div class="row g-4 justify-content-start">
      <?php
      $fitur = [
    [
      "judul" => "Materi",
      "deskripsi" => "Lihat materi lengkap dari tutor profesional.",
      "icon" => "bi-journal-text",
      "warna" => "primary",
      "file" => "materi.php",
      "akses" => "semua"
    ],
    [
      "judul" => "Latihan Soal",
      "deskripsi" => "Uji kemampuanmu dengan latihan soal interaktif.",
      "icon" => "bi-pencil-square",
      "warna" => "success",
      "file" => "soal.php",
      "akses" => "semua"
    ],
    [
      "judul" => "Jadwal Kelas",
      "deskripsi" => "Lihat jadwal kelas online yang telah kamu ikuti.",
      "icon" => "bi-calendar-event-fill",
      "warna" => "secondary",
      "file" => "jadwal_kelas.php",
      "akses" => "semua"
    ],
    [
      "judul" => "Rekap Absensi",
      "deskripsi" => "Pantau kehadiranmu selama mengikuti kelas.",
      "icon" => "bi-clipboard-check-fill",
      "warna" => "dark",
      "file" => "rekap_absensi.php",
      "akses" => "semua"
    ],
    [
      "judul" => "Progress Belajar",
      "deskripsi" => "Lihat perkembangan belajarmu secara berkala.",
      "icon" => "bi-bar-chart-line-fill",
      "warna" => "info",
      "file" => "progres.php",
      "akses" => "semua"
    ]
  ];

      foreach ($fitur as $f) :
        $bisa_akses = true; // Semua yang sudah langganan aktif bisa akses semua fitur
        //$bisa_akses = ($f['akses'] === 'semua') || ($paket === 'premium');
        $link = $bisa_akses ? $f['file'] : '#';
        $style = $bisa_akses ? 'text-dark' : 'text-muted';
        $cardStyle = $bisa_akses ? '' : 'opacity-50';
        $alert = !$bisa_akses ? "onclick=\"alert('Fitur ini hanya tersedia untuk paket Premium.')\"" : '';
      ?>
      <div class="col-12 col-sm-6 col-lg-4">
        <a href="<?= $link ?>" class="text-decoration-none <?= $style ?>" <?= $alert ?>>
          <div class="card shadow-sm h-100 text-center <?= $cardStyle ?>">
            <div class="card-body">
              <i class="bi <?= $f['icon'] ?> fs-1 text-<?= $f['warna'] ?>"></i>
              <h5 class="card-title mt-2"><?= $f['judul'] ?></h5>
              <p class="card-text"><?= $f['deskripsi'] ?></p>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>