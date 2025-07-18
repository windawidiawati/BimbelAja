<?php
include '../includes/auth.php';
include '../includes/header.php';

if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php');
  exit;
}

include '../config/database.php';
$tutor_id = $_SESSION['user']['id'];

// Ambil kelas & kategori
$kelas_result = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
$kategori_result = mysqli_query($conn, "SELECT * FROM kategori_materi ORDER BY nama_kategori ASC");

// Proses hapus soal
if (isset($_GET['hapus'])) {
  $id_hapus = (int) $_GET['hapus'];
  mysqli_query($conn, "DELETE FROM soal WHERE id = $id_hapus AND tutor_id = $tutor_id");
  $success = "Soal berhasil dihapus.";
}

// Proses update soal
if (isset($_POST['update'])) {
  $id_edit    = (int) $_POST['id'];
  $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
  $opsi_a     = mysqli_real_escape_string($conn, $_POST['opsi_a']);
  $opsi_b     = mysqli_real_escape_string($conn, $_POST['opsi_b']);
  $opsi_c     = mysqli_real_escape_string($conn, $_POST['opsi_c']);
  $opsi_d     = mysqli_real_escape_string($conn, $_POST['opsi_d']);
  $jawaban    = mysqli_real_escape_string($conn, $_POST['jawaban']);
  $kelas_id   = (int) $_POST['kelas_id'];
  $kategori_id = (int) $_POST['kategori_id'];

  $query = "UPDATE soal SET 
            pertanyaan = '$pertanyaan',
            opsi_a = '$opsi_a',
            opsi_b = '$opsi_b',
            opsi_c = '$opsi_c',
            opsi_d = '$opsi_d',
            jawaban = '$jawaban',
            kelas_id = $kelas_id,
            kategori_id = $kategori_id
            WHERE id = $id_edit AND tutor_id = $tutor_id";

  if (mysqli_query($conn, $query)) {
    $success = "Soal berhasil diperbarui.";
  } else {
    $error = "Gagal memperbarui soal.";
  }
}

// Proses tambah soal baru
if (isset($_POST['simpan'])) {
  $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
  $opsi_a = mysqli_real_escape_string($conn, $_POST['opsi_a']);
  $opsi_b = mysqli_real_escape_string($conn, $_POST['opsi_b']);
  $opsi_c = mysqli_real_escape_string($conn, $_POST['opsi_c']);
  $opsi_d = mysqli_real_escape_string($conn, $_POST['opsi_d']);
  $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban']);
  $kelas_id = (int) $_POST['kelas_id'];
  $kategori_id = (int) $_POST['kategori_id'];

  $query = "INSERT INTO soal (pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban, tutor_id, kelas_id, kategori_id, created_at)
            VALUES ('$pertanyaan', '$opsi_a', '$opsi_b', '$opsi_c', '$opsi_d', '$jawaban', $tutor_id, $kelas_id, $kategori_id, NOW())";

  if (mysqli_query($conn, $query)) {
    $success = "Soal berhasil ditambahkan.";
  } else {
    $error = "Gagal menyimpan soal.";
  }
}

// Data soal yang akan diedit
$soal_edit = null;
if (isset($_GET['edit'])) {
  $id_edit = (int) $_GET['edit'];
  $result = mysqli_query($conn, "SELECT * FROM soal WHERE id = $id_edit AND tutor_id = $tutor_id");
  $soal_edit = mysqli_fetch_assoc($result);
}

// Ambil semua soal tutor
$query = "SELECT s.*, k.nama_kelas, km.nama_kategori 
          FROM soal s
          LEFT JOIN kelas k ON s.kelas_id = k.id
          LEFT JOIN kategori_materi km ON s.kategori_id = km.id
          WHERE s.tutor_id = $tutor_id 
          ORDER BY s.created_at DESC";
$soal = mysqli_query($conn, $query);
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
    <a href="unggah_materi.php"><i class="bi bi-upload me-2"></i>Unggah Materi</a>
    <a href="buat_soal.php" class="active"><i class="bi bi-pencil-square me-2"></i>Buat Soal</a>
    <a href="jadwal_kelas.php"><i class="bi bi-calendar-event me-2"></i>Jadwal Kelas</a>
    <a href="forum.php"><i class="bi bi-chat-dots me-2"></i>Forum</a>
    <a href="data_siswa.php"><i class="bi bi-people me-2"></i>Data Siswa</a>
</div>

<div class="content">
    <div class="card shadow-sm p-4 mb-5">
        <h3 class="mb-4 text-primary">
            <?= $soal_edit ? '✏ Edit Soal' : '📝 Tambah Soal Baru'; ?>
        </h3>

        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <form method="POST" class="mb-4">
            <?php if ($soal_edit): ?>
                <input type="hidden" name="id" value="<?= $soal_edit['id'] ?>">
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Pertanyaan</label>
                <textarea name="pertanyaan" class="form-control" rows="3" required><?= $soal_edit['pertanyaan'] ?? '' ?></textarea>
            </div>
            <div class="row">
                <?php
                $opsi = ['a', 'b', 'c', 'd'];
                foreach ($opsi as $o):
                ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Opsi <?= strtoupper($o) ?></label>
                    <input type="text" name="opsi_<?= $o ?>" class="form-control" value="<?= $soal_edit['opsi_'.$o] ?? '' ?>" required>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Jawaban Benar (A/B/C/D)</label>
                <input type="text" name="jawaban" class="form-control" maxlength="1" pattern="[A-Da-d]" value="<?= $soal_edit['jawaban'] ?? '' ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php mysqli_data_seek($kelas_result, 0); while ($k = mysqli_fetch_assoc($kelas_result)): ?>
                        <option value="<?= $k['id'] ?>" <?= ($soal_edit && $soal_edit['kelas_id'] == $k['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label">Mata Pelajaran</label>
                <select name="kategori_id" class="form-select" required>
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <?php mysqli_data_seek($kategori_result, 0); while ($k = mysqli_fetch_assoc($kategori_result)): ?>
                        <option value="<?= $k['id'] ?>" <?= ($soal_edit && $soal_edit['kategori_id'] == $k['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="<?= $soal_edit ? 'update' : 'simpan' ?>" class="btn <?= $soal_edit ? 'btn-warning' : 'btn-success' ?> w-100">
                <?= $soal_edit ? '💾 Simpan Perubahan' : '➕ Simpan Soal'; ?>
            </button>
        </form>
    </div>

    <div>
        <h4 class="mb-3">📚 Daftar Soal Anda</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Kelas</th>
                        <th>Pelajaran</th>
                        <th>Pertanyaan</th>
                        <th>A</th>
                        <th>B</th>
                        <th>C</th>
                        <th>D</th>
                        <th>Jawaban</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($soal)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                            <td><?= htmlspecialchars($row['pertanyaan']) ?></td>
                            <td><?= htmlspecialchars($row['opsi_a']) ?></td>
                            <td><?= htmlspecialchars($row['opsi_b']) ?></td>
                            <td><?= htmlspecialchars($row['opsi_c']) ?></td>
                            <td><?= htmlspecialchars($row['opsi_d']) ?></td>
                            <td><strong><?= strtoupper($row['jawaban']) ?></strong></td>
                            <td>
                                <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning mb-1">✏</a>
                                <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Yakin ingin hapus soal ini?')">🗑</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
