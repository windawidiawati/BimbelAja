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

// Ambil data kelas dan kategori
$kelas_result = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
$kategori_result = mysqli_query($conn, "SELECT * FROM kategori_materi ORDER BY nama_kategori ASC");

$edit_id = null;
$topik = $kelas_id = $kategori_id = $tanggal = $waktu_mulai = $waktu_selesai = $link_meeting = "";

// Hapus jadwal
if (isset($_GET['hapus'])) {
  $hapus_id = (int) $_GET['hapus'];
  mysqli_query($conn, "DELETE FROM kelas_online WHERE id = $hapus_id AND tutor_id = $tutor_id");
  $success = "Jadwal kelas berhasil dihapus.";
}

// Edit jadwal (ambil data)
if (isset($_GET['edit'])) {
  $edit_id = (int) $_GET['edit'];
  $res = mysqli_query($conn, "SELECT * FROM kelas_online WHERE id = $edit_id AND tutor_id = $tutor_id");
  if ($res && mysqli_num_rows($res)) {
    $jadwal = mysqli_fetch_assoc($res);
    $topik = $jadwal['topik'];
    $kelas_id = $jadwal['kelas_id'];
    $kategori_id = $jadwal['kategori_id'];
    $tanggal = $jadwal['tanggal'];
    $waktu_mulai = $jadwal['waktu_mulai'];
    $waktu_selesai = $jadwal['waktu_selesai'];
    $link_meeting = $jadwal['link_zoom'];
  } else {
    $error = "Data jadwal tidak ditemukan.";
  }
}

// Simpan data (tambah atau update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $topik         = mysqli_real_escape_string($conn, $_POST['topik']);
  $kelas_id      = (int) $_POST['kelas_id'];
  $kategori_id   = (int) $_POST['kategori_id'];
  $tanggal       = mysqli_real_escape_string($conn, $_POST['tanggal']);
  $waktu_mulai   = mysqli_real_escape_string($conn, $_POST['waktu_mulai']);
  $waktu_selesai = mysqli_real_escape_string($conn, $_POST['waktu_selesai']);
  $link_meeting  = mysqli_real_escape_string($conn, $_POST['link_meeting']);

  if (isset($_POST['edit_id'])) {
    $edit_id = (int) $_POST['edit_id'];
    $query = "UPDATE kelas_online 
              SET topik='$topik', kelas_id=$kelas_id, kategori_id=$kategori_id, tanggal='$tanggal',
                  waktu_mulai='$waktu_mulai', waktu_selesai='$waktu_selesai', link_zoom='$link_meeting'
              WHERE id = $edit_id AND tutor_id = $tutor_id";
    if (mysqli_query($conn, $query)) {
      $success = "Jadwal berhasil diperbarui.";
      $edit_id = null;
      $topik = $kelas_id = $kategori_id = $tanggal = $waktu_mulai = $waktu_selesai = $link_meeting = "";
    } else {
      $error = "Gagal memperbarui jadwal.";
    }
  } else {
    $query = "INSERT INTO kelas_online (topik, kelas_id, kategori_id, tanggal, waktu_mulai, waktu_selesai, link_zoom, tutor_id)
              VALUES ('$topik', $kelas_id, $kategori_id, '$tanggal', '$waktu_mulai', '$waktu_selesai', '$link_meeting', $tutor_id)";
    if (mysqli_query($conn, $query)) {
      $success = "Jadwal kelas berhasil ditambahkan.";
      $topik = $kelas_id = $kategori_id = $tanggal = $waktu_mulai = $waktu_selesai = $link_meeting = "";
    } else {
      $error = "Gagal menambahkan jadwal kelas: " . mysqli_error($conn);
    }
  }
}

// Ambil semua data jadwal
$jadwal_result = mysqli_query($conn, "
  SELECT ko.*, k.nama_kelas, km.nama_kategori 
  FROM kelas_online ko
  LEFT JOIN kelas k ON ko.kelas_id = k.id
  LEFT JOIN kategori_materi km ON ko.kategori_id = km.id
  WHERE ko.tutor_id = $tutor_id 
  ORDER BY ko.tanggal DESC, ko.waktu_mulai DESC
");
?>

<div class="container py-5">
  <h3 class="mb-4"><?= $edit_id ? 'Edit Jadwal Kelas' : 'Tambah Jadwal Kelas Online' ?></h3>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" class="card p-4 shadow-sm mb-4">
    <?php if ($edit_id): ?>
      <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
    <?php endif; ?>

    <div class="mb-3">
      <label>Topik / Judul Meeting</label>
      <input type="text" name="topik" class="form-control" value="<?= htmlspecialchars($topik) ?>" required>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label>Kelas</label>
        <select name="kelas_id" class="form-select" required>
          <option value="">-- Pilih Kelas --</option>
          <?php mysqli_data_seek($kelas_result, 0); while ($k = mysqli_fetch_assoc($kelas_result)): ?>
            <option value="<?= $k['id'] ?>" <?= ($kelas_id == $k['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($k['nama_kelas']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label>Mata Pelajaran</label>
        <select name="kategori_id" class="form-select" required>
          <option value="">-- Pilih Mapel --</option>
          <?php mysqli_data_seek($kategori_result, 0); while ($k = mysqli_fetch_assoc($kategori_result)): ?>
            <option value="<?= $k['id'] ?>" <?= ($kategori_id == $k['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($k['nama_kategori']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label>Tanggal</label>
        <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal) ?>" required>
      </div>
      <div class="col-md-4">
        <label>Waktu Mulai</label>
        <input type="time" name="waktu_mulai" class="form-control" value="<?= htmlspecialchars($waktu_mulai) ?>" required>
      </div>
      <div class="col-md-4">
        <label>Waktu Selesai</label>
        <input type="time" name="waktu_selesai" class="form-control" value="<?= htmlspecialchars($waktu_selesai) ?>" required>
      </div>
    </div>

    <div class="mb-3">
      <label>Link Zoom / Meeting</label>
      <input type="url" name="link_meeting" class="form-control" value="<?= htmlspecialchars($link_meeting) ?>" required>
    </div>

    <button type="submit" class="btn btn-<?= $edit_id ? 'warning' : 'primary' ?>">
      <?= $edit_id ? 'Update Jadwal' : 'Simpan Jadwal' ?>
    </button>
  </form>

  <hr>
  <h5>Daftar Jadwal Kelas:</h5>
  <table class="table table-bordered table-striped mt-3">
    <thead class="table-dark">
      <tr>
        <th>Topik</th>
        <th>Tanggal</th>
        <th>Jam</th>
        <th>Kelas</th>
        <th>Mapel</th>
        <th>Link</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($jadwal_result)): ?>
        <tr>
          <td><?= htmlspecialchars($row['topik']) ?></td>
          <td><?= htmlspecialchars($row['tanggal']) ?></td>
          <td><?= htmlspecialchars($row['waktu_mulai']) ?> - <?= htmlspecialchars($row['waktu_selesai']) ?></td>
          <td><?= htmlspecialchars($row['nama_kelas']) ?: '-' ?></td>
          <td><?= htmlspecialchars($row['nama_kategori']) ?: '-' ?></td>
          <td><a href="<?= htmlspecialchars($row['link_zoom']) ?>" target="_blank">Gabung</a></td>
          <td>
            <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus jadwal ini?')">Hapus</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include '../includes/footer.php'; ?>
