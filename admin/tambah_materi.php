<?php
ob_start();
session_start();
include '../config/database.php';
include '../includes/admin_header.php';

// Pastikan hanya admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Tambah materi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);

    if ($nama) {
        $conn->query("INSERT INTO kategori_materi (nama_kategori) VALUES ('$nama')");
        $_SESSION['success'] = "Mata Pelajaran berhasil ditambahkan";
    } else {
        $_SESSION['error'] = "Nama kategori wajib diisi";
    }
    header("Location: tambah_materi.php");
    exit;
}

// Update materi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id   = intval($_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);

    if ($nama) {
        $conn->query("UPDATE kategori_materi SET nama_kategori='$nama' WHERE id=$id");
        $_SESSION['success'] = "Mata Pelajaran berhasil diperbarui";
    } else {
        $_SESSION['error'] = "Nama kategori wajib diisi";
    }
    header("Location: tambah_materi.php");
    exit;
}

// Hapus materi
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM kategori_materi WHERE id=$id");
    $_SESSION['success'] = "Mata Pelajaran berhasil dihapus";
    header("Location: tambah_materi.php");
    exit;
}

// Ambil semua materi
$materi = $conn->query("SELECT * FROM kategori_materi ORDER BY id DESC");
?>

<div class="content">
    <h3>Tambah Mata Pelajaran</h3>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah Mata Pelajaran</button>

    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered bg-white shadow-sm">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Nama Mata Pelajaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($materi && $materi->num_rows > 0): ?>
                    <?php while ($row = $materi->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEdit<?= $row['id'] ?>">Edit</button>
                                <a href="tambah_materi.php?hapus=<?= $row['id'] ?>" 
                                   onclick="return confirm('Yakin hapus materi ini?')" 
                                   class="btn btn-sm btn-danger">Hapus</a>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEdit<?= $row['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST">
                                        <input type="hidden" name="update" value="1">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <div class="modal-header bg-warning">
                                            <h5 class="modal-title">Edit Mata Pelajaran</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Nama Kategori</label>
                                                <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($row['nama_kategori']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-warning">Update</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center">Belum ada mata pelajaran</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="tambah" value="1">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Mata Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Kategori</label>
                        <input type="text" name="nama_kategori" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#dataTable').DataTable();
});
</script>

<?php include '../includes/admin_footer.php'; ?>
