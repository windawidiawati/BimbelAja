<?php
include '../includes/auth.php';
include '../includes/header.php';

if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php');
  exit;
}

include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $judul     = mysqli_real_escape_string($conn, $_POST['judul']);
  $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  $tutor_id  = $_SESSION['user']['id'];

  $allowed_extensions = ['pdf', 'mp4', 'avi', 'mkv', 'mov'];
  $fileName  = $_FILES['file']['name'];
  $fileTmp   = $_FILES['file']['tmp_name'];
  $ext       = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

  if (in_array($ext, $allowed_extensions)) {
    // Generate nama file unik
    $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);

    // Path tujuan
    $uploadDir = realpath(__DIR__ . '/../assets/uploads');
    if (!$uploadDir) {
      mkdir(__DIR__ . '/../assets/uploads', 0777, true);
      $uploadDir = realpath(__DIR__ . '/../assets/uploads');
    }
    $filePath  = $uploadDir . '/' . $newFileName;

    // Tentukan tipe file
    $tipe_file = ($ext === 'pdf') ? 'pdf' : 'video';

    // Simpan file
    if (move_uploaded_file($fileTmp, $filePath)) {
      $query = "INSERT INTO materi (judul, deskripsi, file, tipe_file, tutor_id)
                VALUES ('$judul', '$deskripsi', '$newFileName', '$tipe_file', $tutor_id)";
      mysqli_query($conn, $query);
      $success = "Materi berhasil diunggah.";
    } else {
      $error = "Gagal mengunggah file.";
    }
  } else {
    $error = "Tipe file tidak diperbolehkan.";
  }
}

// Ambil daftar materi tutor
$tutor_id = $_SESSION['user']['id'];
$materi = mysqli_query($conn, "SELECT * FROM materi WHERE tutor_id = $tutor_id ORDER BY id DESC");
?>

<div class="container mt-5">
  <h3>Unggah Materi</h3>

  <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
  <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="mb-2">
      <label>Judul</label>
      <input type="text" name="judul" class="form-control" required>
    </div>
    <div class="mb-2">
      <label>Deskripsi</label>
      <textarea name="deskripsi" class="form-control" required></textarea>
    </div>
    <div class="mb-2">
      <label>File Materi (PDF atau Video)</label>
      <input type="file" name="file" class="form-control" accept=".pdf,.mp4,.avi,.mkv,.mov" required>
    </div>
    <button type="submit" class="btn btn-primary">Unggah</button>
  </form>

  <hr>
  <h5>Materi yang Telah Diunggah:</h5>
  <ul class="list-group">
    <?php while ($row = mysqli_fetch_assoc($materi)): ?>
      <li class="list-group-item">
        <strong><?= htmlspecialchars($row['judul']) ?></strong><br>
        <small><?= htmlspecialchars($row['deskripsi']) ?></small><br>
        <?php if ($row['tipe_file'] === 'video'): ?>
          <a href="../assets/uploads/<?= htmlspecialchars($row['file']); ?>" target="_blank">Tonton Video</a>
        <?php elseif ($row['tipe_file'] === 'pdf'): ?>
          <a href="../assets/uploads/<?= htmlspecialchars($row['file']); ?>" target="_blank">Lihat PDF</a>
        <?php else: ?>
          <a href="../assets/uploads/<?= htmlspecialchars($row['file']); ?>" target="_blank">Download File</a>
        <?php endif; ?>
      </li>
    <?php endwhile; ?>
  </ul>
</div>

<?php include '../includes/footer.php'; ?>
