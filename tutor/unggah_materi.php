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
  $kategori  = mysqli_real_escape_string($conn, $_POST['kategori']);
  $tutor_id  = $_SESSION['user']['id'];

  $allowed_extensions = ['pdf', 'mp4', 'avi', 'mkv', 'mov'];
  $fileName  = $_FILES['file']['name'];
  $fileTmp   = $_FILES['file']['tmp_name'];
  $ext       = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

  if (in_array($ext, $allowed_extensions)) {
    $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
    $uploadDir = realpath(__DIR__ . '/../assets/uploads');
    if (!$uploadDir) {
      mkdir(__DIR__ . '/../assets/uploads', 0777, true);
      $uploadDir = realpath(__DIR__ . '/../assets/uploads');
    }
    $filePath  = $uploadDir . '/' . $newFileName;
    $tipe_file = ($ext === 'pdf') ? 'pdf' : 'video';

    if (move_uploaded_file($fileTmp, $filePath)) {
      $query = "INSERT INTO materi (judul, deskripsi, kategori, file, tipe_file, tutor_id)
                VALUES ('$judul', '$deskripsi', '$kategori', '$newFileName', '$tipe_file', $tutor_id)";
      mysqli_query($conn, $query);
      $success = "Materi berhasil diunggah.";
    } else {
      $error = "Gagal mengunggah file.";
    }
  } else {
    $error = "Tipe file tidak diperbolehkan.";
  }
}

$tutor_id = $_SESSION['user']['id'];
$materi = mysqli_query($conn, "SELECT * FROM materi WHERE tutor_id = $tutor_id ORDER BY id DESC");
?>

<style>
  .unggah-container {
    max-width: 1000px;
    margin: 100px auto 40px;
  }
</style>

<div class="container unggah-container">
  <div class="card shadow">
    <div class="card-body">
      <h3 class="text-center mb-4">Unggah Materi Baru</h3>

      <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
      <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

      <form method="POST" enctype="multipart/form-data">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Judul</label>
            <input type="text" name="judul" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Kategori</label>
            <input type="text" name="kategori" class="form-control" placeholder="Contoh: Matematika" required>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Deskripsi</label>
          <textarea name="deskripsi" class="form-control" rows="3" required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">File Materi (PDF / Video)</label>
          <input type="file" name="file" class="form-control" accept=".pdf,.mp4,.avi,.mkv,.mov" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Unggah</button>
      </form>
    </div>
  </div>

  <div class="mt-5">
    <h4 class="mb-3">Daftar Materi</h4>
    <?php if (mysqli_num_rows($materi) === 0): ?>
      <div class="alert alert-warning">Belum ada materi yang diunggah.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>Judul</th>
              <th>Kategori</th>
              <th>Deskripsi</th>
              <th>Tipe</th>
              <th>File</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($materi)): ?>
              <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['judul']); ?></td>
                <td><?= htmlspecialchars($row['kategori']); ?></td>
                <td><?= htmlspecialchars($row['deskripsi']); ?></td>
                <td><?= htmlspecialchars($row['tipe_file']); ?></td>
                <td>
                  <?php if ($row['tipe_file'] === 'video'): ?>
                    <a href="../assets/uploads/<?= htmlspecialchars($row['file']); ?>" target="_blank">🎥 Tonton</a>
                  <?php else: ?>
                    <a href="../assets/uploads/<?= htmlspecialchars($row['file']); ?>" target="_blank">📄 Buka</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
