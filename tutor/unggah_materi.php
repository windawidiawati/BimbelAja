<?php
include '../includes/auth.php';
include '../includes/header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php');
  exit;
}

$tutor_id = $_SESSION['user']['id'];

// Ambil data kategori dan kelas
$kategori_result = mysqli_query($conn, "SELECT * FROM kategori_materi");
$kelas_result = mysqli_query($conn, "SELECT * FROM kelas");

// Inisialisasi variabel untuk edit
$edit_id = null;
$judul_edit = $deskripsi_edit = $kategori_edit = $kelas_edit = "";

// Proses Edit
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

// Proses Hapus
if (isset($_GET['hapus'])) {
  $hapus_id = (int) $_GET['hapus'];
  $res = mysqli_query($conn, "SELECT file FROM materi WHERE id = $hapus_id AND tutor_id = $tutor_id");
  if ($res && mysqli_num_rows($res)) {
    $row = mysqli_fetch_assoc($res);
    @unlink(__DIR__ . '/../assets/uploads/' . $row['file']);
    mysqli_query($conn, "DELETE FROM materi WHERE id = $hapus_id AND tutor_id = $tutor_id");
    $success = "Materi berhasil dihapus.";
  }
}

// Proses Upload / Update
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
      $newFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
      $filePath = $uploadDir . '/' . $newFileName;
      $tipe_file = ($ext === 'pdf') ? 'pdf' : 'video';

      if (move_uploaded_file($fileTmp, $filePath)) {
        @unlink($uploadDir . '/' . $file_lama);
        $query = "UPDATE materi SET judul='$judul', deskripsi='$deskripsi', kategori_id=$kategori_id, kelas_id=$kelas_id, file='$newFileName', tipe_file='$tipe_file', status='proses' WHERE id = $id_edit AND tutor_id = $tutor_id";
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
      $newFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
      $filePath = $uploadDir . '/' . $newFileName;
      $tipe_file = ($ext === 'pdf') ? 'pdf' : 'video';

      if (move_uploaded_file($fileTmp, $filePath)) {
        $query = "INSERT INTO materi (judul, deskripsi, kategori_id, kelas_id, file, tipe_file, tutor_id, status, created_at)
                  VALUES ('$judul', '$deskripsi', $kategori_id, $kelas_id, '$newFileName', '$tipe_file', $tutor_id, 'proses', NOW())";
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

// Filter
$filter_kelas = isset($_GET['kelas_id']) ? (int) $_GET['kelas_id'] : 0;
$filter_kategori = isset($_GET['kategori_id']) ? (int) $_GET['kategori_id'] : 0;
$where = "WHERE m.tutor_id = $tutor_id";
if ($filter_kelas > 0) $where .= " AND m.kelas_id = $filter_kelas";
if ($filter_kategori > 0) $where .= " AND m.kategori_id = $filter_kategori";

$query = "SELECT m.*, k.nama_kategori, kl.nama_kelas FROM materi m
          LEFT JOIN kategori_materi k ON m.kategori_id = k.id
          LEFT JOIN kelas kl ON m.kelas_id = kl.id
          $where ORDER BY m.id DESC";
$materi = mysqli_query($conn, $query);
?>

<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f8f9fa;
    }
    .sidebar {
        width: 240px;
        background-color: #0d6efd;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        padding-top: 20px;
    }
    .sidebar a {
        color: white;
        display: block;
        padding: 12px 20px;
        text-decoration: none;
        font-size: 16px;
    }
    .sidebar a:hover {
        background-color: #0b5ed7;
    }
    .sidebar .logo {
        text-align: center;
        margin-bottom: 30px;
        color: white;
        font-size: 20px;
        font-weight: bold;
    }
    .content {
        margin-left: 240px;
        padding: 20px;
    }
</style>

<div class="sidebar">
    <div class="logo">
        <i class="bi bi-mortarboard-fill me-2"></i>BimbelAja
    </div>
    <a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="unggah_materi.php" class="active"><i class="bi bi-upload me-2"></i>Unggah Materi</a>
    <a href="buat_soal.php"><i class="bi bi-pencil-square me-2"></i>Buat Soal</a>
    <a href="jadwal_kelas.php"><i class="bi bi-calendar-event me-2"></i>Jadwal Kelas</a>
    <a href="forum.php"><i class="bi bi-chat-dots me-2"></i>Forum</a>
    <a href="data_siswa.php"><i class="bi bi-people me-2"></i>Data Siswa</a>
</div>

<div class="content">
    <h3 class="mb-4"><?= $edit_id ? 'Edit Materi' : 'Unggah Materi' ?></h3>

    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <!-- Form Upload -->
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

    <!-- Filter -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <select name="kelas_id" class="form-select">
                <option value="0">-- Semua Kelas --</option>
                <?php mysqli_data_seek($kelas_result, 0); while ($k = mysqli_fetch_assoc($kelas_result)): ?>
                    <option value="<?= $k['id'] ?>" <?= ($filter_kelas == $k['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_kelas']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <select name="kategori_id" class="form-select">
                <option value="0">-- Semua Kategori --</option>
                <?php mysqli_data_seek($kategori_result, 0); while ($k = mysqli_fetch_assoc($kategori_result)): ?>
                    <option value="<?= $k['id'] ?>" <?= ($filter_kategori == $k['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-outline-primary w-100">Terapkan Filter</button>
        </div>
    </form>

    <!-- Tabel Materi -->
    <h5>Materi yang Telah Diunggah:</h5>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Kategori</th>
                <th>Kelas</th>
                <th>File</th>
                <th>Status</th>
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
                        <?php if ($row['status'] === 'proses'): ?>
                            <span class="badge bg-warning text-dark">Proses</span>
                        <?php elseif ($row['status'] === 'diterima'): ?>
                            <span class="badge bg-success">Diterima</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Ditolak</span>
                        <?php endif; ?>
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
