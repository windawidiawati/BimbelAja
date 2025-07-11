<?php
include '../includes/auth.php';
include '../includes/header.php';

if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php');
  exit;
}

include '../config/database.php';

$tutor_id = $_SESSION['user']['id'];

$kategori_result = mysqli_query($conn, "SELECT * FROM kategori_materi");
$kelas_result = mysqli_query($conn, "SELECT * FROM kelas");

$edit_id = null;
$judul_edit = $deskripsi_edit = $kategori_edit = $kelas_edit = "";

// Proses hapus materi
if (isset($_GET['hapus'])) {
  $id_hapus = (int) $_GET['hapus'];

  $cek = mysqli_query($conn, "SELECT file FROM materi WHERE id = $id_hapus AND tutor_id = $tutor_id");
  if ($cek && mysqli_num_rows($cek)) {
    $data = mysqli_fetch_assoc($cek);
    $file = $data['file'];
    $file_path = realpath(__DIR__ . '/../assets/uploads/' . $file);
    if ($file_path && file_exists($file_path)) {
      @unlink($file_path);
    }
    mysqli_query($conn, "DELETE FROM materi WHERE id = $id_hapus AND tutor_id = $tutor_id");
    $success = "Materi berhasil dihapus.";
  } else {
    $error = "Materi tidak ditemukan.";
  }
}

// Proses edit ambil data
if (isset($_GET['edit'])) {
  $edit_id = (int) $_GET['edit'];
  $res = mysqli_query($conn, "SELECT * FROM materi WHERE id = $edit_id AND tutor_id = $tutor_id");
  if ($res && mysqli_num_rows($res)) {
    $row_edit = mysqli_fetch_assoc($res);
    $judul_edit = $row_edit['judul'];
    $deskripsi_edit = $row_edit['deskripsi'];
    $kategori_edit = $row_edit['kategori_id'];
    $kelas_edit = $row_edit['kelas_id'];
    $file_lama = $row_edit['file'];
  } else {
    $error = "Materi tidak ditemukan untuk diedit.";
  }
}

// Proses simpan atau update materi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $judul       = mysqli_real_escape_string($conn, $_POST['judul']);
  $deskripsi   = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  $kategori_id = (int) $_POST['kategori_id'];
  $kelas_id    = (int) $_POST['kelas_id'];

  $allowed_extensions = ['pdf', 'mp4', 'avi', 'mkv', 'mov'];
  $fileName = $_FILES['file']['name'];
  $fileTmp  = $_FILES['file']['tmp_name'];
  $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

  $uploadDir = realpath(__DIR__ . '/../assets/uploads');
  if (!$uploadDir) {
    mkdir(__DIR__ . '/../assets/uploads', 0777, true);
    $uploadDir = realpath(__DIR__ . '/../assets/uploads');
  }

  if (isset($_POST['id_edit'])) {
    $id_edit = (int) $_POST['id_edit'];
    if (!empty($fileName) && in_array($ext, $allowed_extensions)) {
      $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
      $filePath = $uploadDir . '/' . $newFileName;
      $tipe_file = ($ext === 'pdf') ? 'pdf' : 'video';

      if (move_uploaded_file($fileTmp, $filePath)) {
        @unlink($uploadDir . '/' . $file_lama);
        $query = "UPDATE materi SET judul='$judul', deskripsi='$deskripsi', kategori_id=$kategori_id, kelas_id=$kelas_id, file='$newFileName', tipe_file='$tipe_file' WHERE id = $id_edit AND tutor_id = $tutor_id";
      }
    } else {
      $query = "UPDATE materi SET judul='$judul', deskripsi='$deskripsi', kategori_id=$kategori_id, kelas_id=$kelas_id WHERE id = $id_edit AND tutor_id = $tutor_id";
    }

    if (isset($query) && mysqli_query($conn, $query)) {
      $success = "Materi berhasil diperbarui.";
      $edit_id = null;
    } else {
      $error = "Gagal memperbarui materi.";
    }
  } else {
    if (in_array($ext, $allowed_extensions)) {
      $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
      $filePath = $uploadDir . '/' . $newFileName;
      $tipe_file = ($ext === 'pdf') ? 'pdf' : 'video';

      if (move_uploaded_file($fileTmp, $filePath)) {
        $query = "INSERT INTO materi (judul, deskripsi, kategori_id, kelas_id, file, tipe_file, tutor_id, created_at)
                  VALUES ('$judul', '$deskripsi', $kategori_id, $kelas_id, '$newFileName', '$tipe_file', $tutor_id, NOW())";
        mysqli_query($conn, $query);
        $success = "Materi berhasil diunggah.";
      } else {
        $error = "Gagal mengunggah file.";
      }
    } else {
      $error = "Tipe file tidak diperbolehkan.";
    }
  }
}

$query = "SELECT m.*, k.nama_kategori, kl.nama_kelas FROM materi m
          LEFT JOIN kategori_materi k ON m.kategori_id = k.id
          LEFT JOIN kelas kl ON m.kelas_id = kl.id
          WHERE m.tutor_id = $tutor_id ORDER BY m.id DESC";
$materi = mysqli_query($conn, $query);
?>

<div class="container mt-5 mb-5">
  <h3><?= $edit_id ? 'Edit Materi' : 'Unggah Materi' ?></h3>

  <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
  <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

  <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm mb-4">
    <?php if ($edit_id): ?>
      <input type="hidden" name="id_edit" value="<?= $edit_id ?>">
    <?php endif; ?>

    <div class="mb-3">
      <label>Judul Materi</label>
      <input type="text" name="judul" class="form-control" required value="<?= htmlspecialchars($judul_edit) ?>">
    </div>
    <div class="mb-3">
      <label>Deskripsi</label>
      <textarea name="deskripsi" class="form-control" required><?= htmlspecialchars($deskripsi_edit) ?></textarea>
    </div>
    <div class="mb-3">
      <label>Kategori</label>
      <select name="kategori_id" class="form-select" required>
        <option value="">-- Pilih Kategori --</option>
        <?php mysqli_data_seek($kategori_result, 0); while ($k = mysqli_fetch_assoc($kategori_result)): ?>
          <option value="<?= $k['id'] ?>" <?= ($k['id'] == $kategori_edit) ? 'selected' : '' ?>>
            <?= htmlspecialchars($k['nama_kategori']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Kelas</label>
      <select name="kelas_id" class="form-select" required>
        <option value="">-- Pilih Kelas --</option>
        <?php mysqli_data_seek($kelas_result, 0); while ($kl = mysqli_fetch_assoc($kelas_result)): ?>
          <option value="<?= $kl['id'] ?>" <?= ($kl['id'] == $kelas_edit) ? 'selected' : '' ?>>
            <?= htmlspecialchars($kl['nama_kelas']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>File Materi (PDF/Video)</label>
      <input type="file" name="file" class="form-control" accept=".pdf,.mp4,.avi,.mkv,.mov">
    </div>
    <button type="submit" class="btn btn-<?= $edit_id ? 'warning' : 'primary' ?>">
      <?= $edit_id ? 'Update Materi' : 'Unggah Materi' ?>
    </button>
  </form>

  <h5>Materi yang Telah Diunggah:</h5>
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Judul</th>
        <th>Deskripsi</th>
        <th>Kategori</th>
        <th>Kelas</th>
        <th>File</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($materi)): ?>
        <tr>
          <td><?= htmlspecialchars($row['judul']) ?></td>
          <td><?= htmlspecialchars($row['deskripsi']) ?></td>
          <td><?= htmlspecialchars($row['nama_kategori']) ?: '-' ?></td>
          <td><?= htmlspecialchars($row['nama_kelas']) ?: '-' ?></td>
          <td>
            <a href="../assets/uploads/<?= htmlspecialchars($row['file']) ?>" target="_blank">
              <?= ($row['tipe_file'] === 'video') ? 'Tonton Video' : 'Lihat PDF'; ?>
            </a>
          </td>
          <td>
            <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus materi ini?')">Hapus</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include '../includes/footer.php'; ?>
