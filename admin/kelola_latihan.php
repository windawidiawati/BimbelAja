<?php
include '../config/database.php';
include '../includes/admin_header.php';

// ==== Handle Tambah ====
if (isset($_POST['tambah'])) {
    $judul = $_POST['judul'];
    $kelas_id = $_POST['kelas_id'];
    $kategori_id = $_POST['kategori_id'];
    $paket_id = $_POST['paket_id'];
    $tanggal_publish = $_POST['tanggal_publish'];
    $tenggat_waktu = $_POST['tenggat_waktu'];
    $tutor_id = 1; // static contoh

    $stmt = $conn->prepare("INSERT INTO latihan (judul, tutor_id, kelas_id, kategori_id, paket_id, tanggal_publish, tenggat_waktu, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("siiiiss", $judul, $tutor_id, $kelas_id, $kategori_id, $paket_id, $tanggal_publish, $tenggat_waktu);
    $stmt->execute();
}

// ==== Handle Edit ====
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $judul = $_POST['judul'];
    $kelas_id = $_POST['kelas_id'];
    $kategori_id = $_POST['kategori_id'];
    $paket_id = $_POST['paket_id'];
    $tanggal_publish = $_POST['tanggal_publish'];
    $tenggat_waktu = $_POST['tenggat_waktu'];

    $stmt = $conn->prepare("UPDATE latihan SET judul=?, kelas_id=?, kategori_id=?, paket_id=?, tanggal_publish=?, tenggat_waktu=? WHERE id=?");
    $stmt->bind_param("siiissi", $judul, $kelas_id, $kategori_id, $paket_id, $tanggal_publish, $tenggat_waktu, $id);
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

$sql = "SELECT l.*, 
               k.nama_kelas, 
               km.nama_kategori, 
               p.nama AS nama_paket
        FROM latihan l
        LEFT JOIN kelas k ON l.kelas_id = k.id
        LEFT JOIN kategori_materi km ON l.kategori_id = km.id
        LEFT JOIN paket p ON l.paket_id = p.id
        WHERE 1";
if (!empty($kelasFilter)) $sql .= " AND l.kelas_id = '$kelasFilter'";
if (!empty($kategoriFilter)) $sql .= " AND l.kategori_id = '$kategoriFilter'";
$result = $conn->query($sql);

// Fetch Kelas, Kategori Materi, dan Paket untuk dropdowns
$result_kelas = $conn->query("SELECT * FROM kelas");
$result_kategori = $conn->query("SELECT * FROM kategori_materi");
$result_paket = $conn->query("SELECT * FROM paket");
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
                <?php endwhile; $result_kelas->data_seek(0); ?>
            </select>
            <select name="kategori" class="form-control mr-2">
                <option value="">-- Semua Pelajaran --</option>
                <?php while ($kategori = $result_kategori->fetch_assoc()): ?>
                    <option value="<?= $kategori['id'] ?>" <?= $kategoriFilter == $kategori['id'] ? 'selected' : '' ?>><?= $kategori['nama_kategori'] ?></option>
                <?php endwhile; $result_kategori->data_seek(0); ?>
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
                        <th>Paket</th>
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
                        <td><?= $row['nama_kelas'] ?></td>
                        <td><?= $row['nama_kategori'] ?></td>
                        <td><?= $row['nama_paket'] ?></td>
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
                                                <?php $result_kelas->data_seek(0); while ($kelas = $result_kelas->fetch_assoc()): ?>
                                                    <option value="<?= $kelas['id'] ?>" <?= $row['kelas_id'] == $kelas['id'] ? 'selected' : '' ?>><?= $kelas['nama_kelas'] ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="form-group"><label>Pelajaran</label>
                                            <select name="kategori_id" class="form-control">
                                                <?php $result_kategori->data_seek(0); while ($kategori = $result_kategori->fetch_assoc()): ?>
                                                    <option value="<?= $kategori['id'] ?>" <?= $row['kategori_id'] == $kategori['id'] ? 'selected' : '' ?>><?= $kategori['nama_kategori'] ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="form-group"><label>Paket</label>
                                            <select name="paket_id" class="form-control">
                                                <?php $result_paket->data_seek(0); while ($paket = $result_paket->fetch_assoc()): ?>
                                                    <option value="<?= $paket['id'] ?>" <?= $row['paket_id'] == $paket['id'] ? 'selected' : '' ?>><?= $paket['nama'] ?></option>
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
                    <div class="form-group"><label>Judul</label><input type="text" name="judul" class="form-control" required></div>
                    <div class="form-group"><label>Kelas</label>
                        <select name="kelas_id" class="form-control" required>
                            <?php $result_kelas->data_seek(0); while ($kelas = $result_kelas->fetch_assoc()): ?>
                                <option value="<?= $kelas['id'] ?>"><?= $kelas['nama_kelas'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Pelajaran</label>
                        <select name="kategori_id" class="form-control" required>
                            <?php $result_kategori->data_seek(0); while ($kategori = $result_kategori->fetch_assoc()): ?>
                                <option value="<?= $kategori['id'] ?>"><?= $kategori['nama_kategori'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Paket</label>
                        <select name="paket_id" class="form-control" required>
                            <?php $result_paket->data_seek(0); while ($paket = $result_paket->fetch_assoc()): ?>
                                <option value="<?= $paket['id'] ?>"><?= $paket['nama'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Publish</label><input type="date" name="tanggal_publish" class="form-control" required></div>
                    <div class="form-group"><label>Tenggat</label><input type="date" name="tenggat_waktu" class="form-control" required></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success">Tambah</button></div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
