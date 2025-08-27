<?php
include '../includes/auth.php';
include '../includes/tutor_header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'tutor') {
    header('Location: ../index.php');
    exit;
}

$tutor_id = $_SESSION['user']['id'];

$kategori_result = mysqli_query($conn, "SELECT * FROM kategori_materi");
$kelas_result = mysqli_query($conn, "SELECT * FROM kelas");
$paket_result = mysqli_query($conn, "SELECT id, nama FROM paket WHERE status = 'aktif'");

$edit_id = null;
$judul_edit = $deskripsi_edit = $kategori_edit = $kelas_edit = $paket_edit = "";

// EDIT
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM materi WHERE id = $edit_id AND tutor_id = $tutor_id");
    if ($res && mysqli_num_rows($res)) {
        $row_edit = mysqli_fetch_assoc($res);
        $judul_edit = $row_edit['judul'];
        $deskripsi_edit = $row_edit['deskripsi'];
        $kategori_edit = $row_edit['kategori_id'];
        $kelas_edit = $row_edit['kelas_id'];
        $paket_edit = $row_edit['paket_id'];
        $file_lama = $row_edit['file'];
    } else {
        $error = "Materi tidak ditemukan untuk diedit.";
    }
}

// HAPUS
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

// UPLOAD / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul       = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi   = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $kategori_id = (int) $_POST['kategori_id'];
    $kelas_id    = (int) $_POST['kelas_id'];
    $paket_id    = (int) $_POST['paket_id'];

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
            $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
            $filePath = $uploadDir . '/' . $newFileName;
            $tipe_file = ($ext === 'pdf') ? 'pdf' : 'video';

            if (move_uploaded_file($fileTmp, $filePath)) {
                @unlink($uploadDir . '/' . $file_lama);
                $query = "UPDATE materi 
                          SET judul='$judul', deskripsi='$deskripsi', kategori_id=$kategori_id, kelas_id=$kelas_id, 
                              paket_id=$paket_id, file='$newFileName', tipe_file='$tipe_file', status='proses' 
                          WHERE id = $id_edit AND tutor_id = $tutor_id";
            }
        } else {
            $query = "UPDATE materi 
                      SET judul='$judul', deskripsi='$deskripsi', kategori_id=$kategori_id, kelas_id=$kelas_id, paket_id=$paket_id 
                      WHERE id = $id_edit AND tutor_id = $tutor_id";
        }

        if (isset($query) && mysqli_query($conn, $query)) {
            $success = "Materi berhasil diperbarui.";
            $edit_id = null;
        } else {
            $error = "Gagal memperbarui materi.";
        }
    } else {
        if (in_array($ext, $allowed_extensions)) {
            $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
            $filePath = $uploadDir . '/' . $newFileName;
            $tipe_file = ($ext === 'pdf') ? 'pdf' : 'video';

            if (move_uploaded_file($fileTmp, $filePath)) {
                $query = "INSERT INTO materi (judul, deskripsi, kategori_id, kelas_id, paket_id, file, tipe_file, tutor_id, status, created_at)
                          VALUES ('$judul', '$deskripsi', $kategori_id, $kelas_id, $paket_id, '$newFileName', '$tipe_file', $tutor_id, 'proses', NOW())";
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

// FILTER
$filter_kelas = isset($_GET['kelas_id']) ? (int) $_GET['kelas_id'] : 0;
$filter_kategori = isset($_GET['kategori_id']) ? (int) $_GET['kategori_id'] : 0;
$where = "WHERE m.tutor_id = $tutor_id";
if ($filter_kelas > 0) $where .= " AND m.kelas_id = $filter_kelas";
if ($filter_kategori > 0) $where .= " AND m.kategori_id = $filter_kategori";

$query = "SELECT m.*, k.nama_kategori, kl.nama_kelas, p.nama AS nama_paket
          FROM materi m
          LEFT JOIN kategori_materi k ON m.kategori_id = k.id
          LEFT JOIN kelas kl ON m.kelas_id = kl.id
          LEFT JOIN paket p ON m.paket_id = p.id
          $where ORDER BY m.id DESC";
$materi = mysqli_query($conn, $query);
?>

<div class="content">
    <h4 class="fw-bold mb-4"><i class="bi bi-upload me-2"></i><?= $edit_id ? 'Edit Materi' : 'Unggah Materi' ?></h4>

    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <!-- FORM UPLOAD / EDIT -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
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
                    <label>Paket</label>
                    <select name="paket_id" class="form-select" required>
                        <option value="">-- Pilih Paket --</option>
                        <?php mysqli_data_seek($paket_result, 0); while ($p = mysqli_fetch_assoc($paket_result)): ?>
                            <option value="<?= $p['id'] ?>" <?= ($p['id'] == $paket_edit) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>File Materi (PDF/Video)</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.mp4,.avi,.mkv,.mov">
                </div>
                <button type="submit" class="btn btn-<?= $edit_id ? 'warning' : 'primary' ?> w-100">
                    <?= $edit_id ? 'Update Materi' : 'Unggah Materi' ?>
                </button>
            </form>
        </div>
    </div>

    <!-- FILTER -->
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

    <div class="alert alert-warning"><strong>Catatan:</strong> Materi akan dicek oleh admin sebelum tampil ke siswa.</div>

    <!-- TABEL MATERI -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <b>📚 Daftar Materi</b>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Judul</th>
                        <th>Deskripsi</th>
                        <th>Kategori</th>
                        <th>Kelas</th>
                        <th>Paket</th>
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
                            <td><?= htmlspecialchars($row['nama_paket']) ?: '-' ?></td>
                            <td>
                                <a href="../assets/uploads/<?= htmlspecialchars($row['file']) ?>" target="_blank">
                                    <?= ($row['tipe_file'] === 'video') ? 'Tonton Video' : 'Lihat PDF'; ?>
                                </a>
                            </td>
                            <td>
                                <?php 
                                    $status_badge = [
                                        'proses' => 'warning text-dark',
                                        'diterima' => 'success',
                                        'ditolak' => 'danger'
                                    ];
                                ?>
                                <span class="badge bg-<?= $status_badge[$row['status']] ?? 'secondary' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
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
    </div>

</div>

<?php include '../includes/tutor_footer.php'; ?>
