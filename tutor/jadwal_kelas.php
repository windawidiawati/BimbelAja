<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php');
  exit;
}
include '../includes/header.php';
include '../config/database.php';

$tutor_id = $_SESSION['user']['id'];
$success = $error = "";

// Menyimpan kelas baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama_kelas     = mysqli_real_escape_string($conn, $_POST['nama_kelas']);
  $tanggal        = mysqli_real_escape_string($conn, $_POST['tanggal']);
  $waktu_mulai    = mysqli_real_escape_string($conn, $_POST['waktu_mulai']);
  $waktu_selesai  = mysqli_real_escape_string($conn, $_POST['waktu_selesai']);
  $link_meeting   = mysqli_real_escape_string($conn, $_POST['link_meeting']);

  $query = "INSERT INTO kelas_online (nama_kelas, tanggal, waktu_mulai, waktu_selesai, link_zoom, tutor_id)
            VALUES ('$nama_kelas', '$tanggal', '$waktu_mulai', '$waktu_selesai', '$link_meeting', $tutor_id)";
  if (mysqli_query($conn, $query)) {
    $success = "Jadwal kelas berhasil ditambahkan.";
  } else {
    $error = "Gagal menambahkan jadwal kelas: " . mysqli_error($conn);
  }
}

// Ambil daftar kelas
$kelas = mysqli_query($conn, "SELECT * FROM kelas_online WHERE tutor_id = $tutor_id ORDER BY tanggal DESC");
?>

<div class="container py-5">
  <h3 class="mb-4">Jadwal Kelas</h3>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" class="mb-4">
    <div class="row mb-2">
      <div class="col-md-6">
        <label>Nama Kelas</label>
        <input type="text" name="nama_kelas" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label>Tanggal</label>
        <input type="date" name="tanggal" class="form-control" required>
      </div>
    </div>
    <div class="row mb-2">
      <div class="col-md-6">
        <label>Waktu Mulai</label>
        <input type="time" name="waktu_mulai" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label>Waktu Selesai</label>
        <input type="time" name="waktu_selesai" class="form-control" required>
      </div>
    </div>
    <div class="mb-3">
      <label>Link Zoom / Meeting</label>
      <input type="text" name="link_meeting" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
  </form>

  <hr>
  <h5>Daftar Kelas:</h5>
  <table class="table table-striped mt-3">
    <thead>
      <tr>
        <th>Nama Kelas</th>
        <th>Tanggal</th>
        <th>Jam</th>
        <th>Link Meeting</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($kelas)): ?>
        <tr>
          <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
          <td><?= htmlspecialchars($row['tanggal']) ?></td>
          <td><?= htmlspecialchars($row['waktu_mulai']) ?> - <?= htmlspecialchars($row['waktu_selesai']) ?></td>
          <td><a href="<?= htmlspecialchars($row['link_zoom']) ?>" target="_blank">Gabung</a></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include '../includes/footer.php'; ?>
