<?php
include '../includes/auth.php';
include '../includes/header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'siswa') {
  header('Location: ../index.php'); exit;
}

$user_id = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'];

// Ambil jenis paket langganan aktif siswa
$query = "SELECT paket FROM langganan WHERE user_id = $user_id AND status = 'aktif' ORDER BY created_at DESC LIMIT 1";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$paket = $row['paket'] ?? 'none';
?>

<div class="container mt-5">
  <div class="text-center mb-4">
    <h2 class="fw-bold">Dashboard Siswa</h2>
    <p>Halo, <strong><?= htmlspecialchars($username); ?></strong>. Selamat belajar dan semangat terus!</p>
    <p class="badge bg-info text-dark">Paket Langganan: <strong><?= ucfirst($paket); ?></strong></p>
  </div>

  <div class="row row-cols-1 row-cols-md-3 g-4">
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
        "judul" => "Kelas Online",
        "deskripsi" => "Ikuti kelas online langsung bersama tutor.",
        "icon" => "bi-camera-video-fill",
        "warna" => "danger",
        "file" => "kelas_online.php",
        "akses" => "premium"
      ],
      [
        "judul" => "Forum Diskusi",
        "deskripsi" => "Diskusi bersama teman dan tutor.",
        "icon" => "bi-chat-dots-fill",
        "warna" => "warning",
        "file" => "forum.php",
        "akses" => "premium"
      ],
      [
        "judul" => "Progress Belajar",
        "deskripsi" => "Lihat perkembangan belajarmu secara berkala.",
        "icon" => "bi-bar-chart-line-fill",
        "warna" => "info",
        "file" => "progress.php",
        "akses" => "premium"
      ]
    ];

    foreach ($fitur as $f) :
      $bisa_akses = ($f['akses'] === 'semua') || ($paket === 'premium');
      $link = $bisa_akses ? $f['file'] : '#';
      $style = $bisa_akses ? 'text-dark' : 'text-muted';
      $cardStyle = $bisa_akses ? '' : 'opacity-50';
      $alert = !$bisa_akses ? "onclick=\"alert('Fitur ini hanya tersedia untuk paket Premium.')\"" : '';
    ?>
    <div class="col">
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
