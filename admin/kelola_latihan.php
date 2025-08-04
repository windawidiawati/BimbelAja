
<?php
include '../config/database.php';
include '../includes/admin_header.php';

// ==== Handle Tambah ====
if (isset($_POST['tambah'])) {
    $judul = $_POST['judul'];
    $kelas_id = $_POST['kelas_id'];
    $kategori_id = $_POST['kategori_id'];
    $tanggal_publish = $_POST['tanggal_publish'];
    $tenggat_waktu = $_POST['tenggat_waktu'];
    $tutor_id = 1; // Assuming a static tutor ID for demonstration

    $stmt = $conn->prepare("INSERT INTO latihan (judul, tutor_id, kelas_id, kategori_id, tanggal_publish, tenggat_waktu, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("siiiis", $judul, $tutor_id, $kelas_id, $kategori_id, $tanggal_publish, $tenggat_waktu);
    $stmt->execute();
}

// ==== Handle Edit ====
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $judul = $_POST['judul'];
    $kelas_id = $_POST['kelas_id'];
    $kategori_id = $_POST['kategori_id'];
    $tanggal_publish = $_POST['tanggal_publish'];
    $tenggat_waktu = $_POST['tenggat_waktu'];

    $stmt = $conn->prepare("UPDATE latihan SET judul=?, kelas_id=?, kategori_id=?, tanggal_publish=?, tenggat_waktu=? WHERE id=?");
    $stmt->bind_param("siissi", $judul, $kelas_id, $kategori_id, $tanggal_publish, $tenggat_waktu, $id);
    $stmt->execute();
}

// ==== Handle Hapus ====
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM latihan WHERE id = $id");
}

// ==== Filter ====
$kelasFilter = $_GET['kelas'] ?? '';
$kategoriFilter = $_GET['kategori'] ?? '';

$sql = "SELECT * FROM latihan WHERE 1";
if (!empty($kelasFilter)) $sql .= " AND kelas_id = '$kelasFilter'";
if (!empty($kategoriFilter)) $sql .= " AND kategori_id = '$kategoriFilter'";
$result = $conn->query($sql);

// Fetch Kelas and Kategori Materi for dropdowns
$sql_kelas = "SELECT * FROM kelas";
$result_kelas = $conn->query($sql_kelas);

$sql_kategori = "SELECT * FROM kategori_materi";
$result_kategori = $conn->query($sql_kategori);
?>

<div class="content">
    <div class="container mt-4">
        <h4 class="mb-4">Kelola Latihan</h4>

        <!-- Filter -->
        <form method="get" class="form-inline mb-3">
            <select name="kelas" class="form-control mr-2">
                <option value="">-- Semua Kelas --</option>
                <?php while ($kelas = $result_kelas->fetch_assoc()): ?>
                    <option value="<?= $kelas['id'] ?>" <?= $kelasFilter == $kelas['id'] ? 'selected' : '' ?>><?= $kelas['nama_kelas'] ?></option>
                <?php endwhile; ?>
            </select>
            <select name="kategori" class="form-control mr-2">
                <option value="">-- Semua Pelajaran --</option>
                <?php while ($kategori = $result_kategori->fetch_assoc()): ?>
                    <option value="<?= $kategori['id'] ?>" <?= $kategoriFilter == $kategori['id'] ? 'selected' : '' ?>><?= $kategori['nama_kategori'] ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <!-- Tombol Tambah -->
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#tambahModal">+ Tambah Latihan</button>

        <!-- Tabel -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-light">
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Kelas</th>
                        <th>Pelajaran</th>
                        <th>Publish</th>
                        <th>Tenggat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $row['judul'] ?></td>
                        <td><?= $row['kelas_id'] ?></td>
                        <td><?= $row['kategori_id'] ?></td>
                        <td><?= date("d M Y", strtotime($row['tanggal_publish'])) ?></td>
                        <td><?= date("d M Y", strtotime($row['tenggat_waktu'])) ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                            <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin hapus?')" class="btn btn-danger btn-sm">Hapus</a>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="post">
                                <input type="hidden" name="edit" value="1">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5>Edit Latihan</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group"><label>Judul</label><input type="text" name="judul" class="form-control" value="<?= $row['judul'] ?>"></div>
                                        <div class="form-group"><label>Kelas</label>
                                            <select name="kelas_id" class="form-control">
                                                <?php
                                                // Reset the result set for kelas
                                                $result_kelas->data_seek(0);
                                                while ($kelas = $result_kelas->fetch_assoc()): ?>
                                                    <option value="<?= $kelas['id'] ?>" <?= $row['kelas_id'] == $kelas['id'] ? 'selected' : '' ?>><?= $kelas['nama_kelas'] ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="form-group"><label>Pelajaran</label>
                                            <select name="kategori_id" class="form-control">
                                                <?php
                                                // Reset the result set for kategori
                                                $result_kategori->data_seek(0);
                                                while ($kategori = $result_kategori->fetch_assoc()): ?>
                                                    <option value="<?= $kategori['id'] ?>" <?= $row['kategori_id'] == $kategori['id'] ? 'selected' : '' ?>><?= $kategori['nama_kategori'] ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="form-group"><label>Publish</label><input type="date" name="tanggal_publish" class="form-control" value="<?= $row['tanggal_publish'] ?>"></div>
                                        <div class="form-group"><label>Tenggat</label><input type="date" name="tenggat_waktu" class="form-control" value="<?= $row['tenggat_waktu'] ?>"></div>
                                    </div>
                                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

      <!-- Modal Tambah -->
<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post">
            <input type="hidden" name="tambah" value="1">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Tambah Latihan</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Judul</label>
                        <input type="text" name="judul" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas_id" class="form-control" required>
                            <?php
                            // Query ulang kelas agar dropdown tidak kosong
                            $sql_kelas = "SELECT * FROM kelas";
                            $result_kelas_modal = $conn->query($sql_kelas);
                            while ($kelas = $result_kelas_modal->fetch_assoc()):
                            ?>
                                <option value="<?= $kelas['id'] ?>"><?= $kelas['nama_kelas'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Pelajaran</label>
                        <select name="kategori_id" class="form-control" required>
                            <option value="">Pilih Mata Pelajaran</option>
                            <?php
                            // Query ulang kategori agar dropdown tampil semua pilihan
                            $sql_kategori = "SELECT * FROM kategori_materi";
                            $result_kategori_modal = $conn->query($sql_kategori);
                            while ($kategori = $result_kategori_modal->fetch_assoc()):
                            ?>
                                <option value="<?= $kategori['id'] ?>"><?= $kategori['nama_kategori'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Publish</label>
                        <input type="date" name="tanggal_publish" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Tenggat</label>
                        <input type="date" name="tenggat_waktu" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Tambah</button>
                </div>
            </div>
        </form>
    </div>
</div>


<?php include '../includes/footer.php'; ?>

<!-- Include Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>