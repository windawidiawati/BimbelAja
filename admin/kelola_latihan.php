<?php
// pastikan tidak ada output sebelum ini (tidak ada spasi/line break sebelum <?php)
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
    if ($stmt) {
        // bind types: judul (s), tutor_id (i), kelas_id (i), kategori_id (i), paket_id (i), tanggal_publish (s), tenggat_waktu (s)
        $stmt->bind_param("siiiiss", $judul, $tutor_id, $kelas_id, $kategori_id, $paket_id, $tanggal_publish, $tenggat_waktu);
        $stmt->execute();
        $stmt->close();
    }
}

// ==== Handle Edit ====
// Perubahan penting: selain update field lain, kita juga set created_at = NOW()
// sehingga record yang diupdate menjadi "terbaru" dan akan muncul paling atas saat di-order by created_at DESC
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $judul = $_POST['judul'];
    $kelas_id = intval($_POST['kelas_id']);
    $kategori_id = intval($_POST['kategori_id']);
    $paket_id = intval($_POST['paket_id']);
    $tanggal_publish = $_POST['tanggal_publish'];
    $tenggat_waktu = $_POST['tenggat_waktu'];

    // NOTE: kita juga set created_at = NOW() agar update muncul paling atas
    $stmt = $conn->prepare("UPDATE latihan SET judul=?, kelas_id=?, kategori_id=?, paket_id=?, tanggal_publish=?, tenggat_waktu=?, created_at = NOW() WHERE id=?");
    if ($stmt) {
        // types: judul (s), kelas_id (i), kategori_id (i), paket_id (i), tanggal_publish (s), tenggat_waktu (s), id (i)
        $stmt->bind_param("siiissi", $judul, $kelas_id, $kategori_id, $paket_id, $tanggal_publish, $tenggat_waktu, $id);
        $stmt->execute();
        $stmt->close();
    }
}

// ==== Handle Hapus ====
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM latihan WHERE id = $id");
}

// ==== Filter ====
$kelasFilter = $_GET['kelas'] ?? '';
$kategoriFilter = $_GET['kategori'] ?? '';

// ==== Query utama: tambahkan ORDER BY l.created_at DESC agar data terbaru (create/update) tampil paling atas ====
$sql = "SELECT l.*, 
               k.nama_kelas, 
               km.nama_kategori, 
               p.nama AS nama_paket
        FROM latihan l
        LEFT JOIN kelas k ON l.kelas_id = k.id
        LEFT JOIN kategori_materi km ON l.kategori_id = km.id
        LEFT JOIN paket p ON l.paket_id = p.id
        WHERE 1";
if (!empty($kelasFilter)) $sql .= " AND l.kelas_id = '" . $conn->real_escape_string($kelasFilter) . "'";
if (!empty($kategoriFilter)) $sql .= " AND l.kategori_id = '" . $conn->real_escape_string($kategoriFilter) . "'";

// order by created_at DESC (baru/diupdate di atas). Jika tabelmu belum punya created_at, insert sudah memakai NOW() sehingga bekerja.
$sql .= " ORDER BY l.created_at DESC";

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
                <?php 
                // gunakan data_seek agar pointer bisa dikembalikan jika dipakai lagi
                if ($result_kelas) {
                    while ($kelas = $result_kelas->fetch_assoc()): ?>
                        <option value="<?= $kelas['id'] ?>" <?= ($kelasFilter == $kelas['id']) ? 'selected' : '' ?>><?= htmlspecialchars($kelas['nama_kelas']) ?></option>
                <?php endwhile; $result_kelas->data_seek(0); } ?>
            </select>

            <select name="kategori" class="form-control mr-2">
                <option value="">-- Semua Pelajaran --</option>
                <?php 
                if ($result_kategori) {
                    while ($kategori = $result_kategori->fetch_assoc()): ?>
                        <option value="<?= $kategori['id'] ?>" <?= ($kategoriFilter == $kategori['id']) ? 'selected' : '' ?>><?= htmlspecialchars($kategori['nama_kategori']) ?></option>
                <?php endwhile; $result_kategori->data_seek(0); } ?>
            </select>

            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <!-- Tombol Tambah -->
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#tambahModal">+ Tambah Latihan</button>

        <!-- Tabel -->
        <div class="table-responsive">
            <table id="latihanTable" class="table table-bordered table-striped">
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
                    <?php $no = 1; if ($result): while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                        <td><?= !empty($row['tanggal_publish']) ? date("d M Y", strtotime($row['tanggal_publish'])) : '-' ?></td>
                        <td><?= !empty($row['tenggat_waktu']) ? date("d M Y", strtotime($row['tenggat_waktu'])) : '-' ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= intval($row['id']) ?>">Edit</button>
                            <a href="?hapus=<?= intval($row['id']) ?>" onclick="return confirm('Yakin ingin hapus?')" class="btn btn-danger btn-sm">Hapus</a>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editModal<?= intval($row['id']) ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="post">
                                <input type="hidden" name="edit" value="1">
                                <input type="hidden" name="id" value="<?= intval($row['id']) ?>">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5>Edit Latihan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group mb-2"><label>Judul</label><input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($row['judul']) ?>"></div>

                                        <div class="form-group mb-2"><label>Kelas</label>
                                            <select name="kelas_id" class="form-control">
                                                <?php 
                                                if ($result_kelas) {
                                                    $result_kelas->data_seek(0);
                                                    while ($kelas = $result_kelas->fetch_assoc()):
                                                ?>
                                                    <option value="<?= intval($kelas['id']) ?>" <?= ($row['kelas_id']==$kelas['id']) ? 'selected' : '' ?>><?= htmlspecialchars($kelas['nama_kelas']) ?></option>
                                                <?php endwhile; $result_kelas->data_seek(0); } ?>
                                            </select>
                                        </div>

                                        <div class="form-group mb-2"><label>Pelajaran</label>
                                            <select name="kategori_id" class="form-control">
                                                <?php 
                                                if ($result_kategori) {
                                                    $result_kategori->data_seek(0);
                                                    while ($kategori = $result_kategori->fetch_assoc()):
                                                ?>
                                                    <option value="<?= intval($kategori['id']) ?>" <?= ($row['kategori_id']==$kategori['id']) ? 'selected' : '' ?>><?= htmlspecialchars($kategori['nama_kategori']) ?></option>
                                                <?php endwhile; $result_kategori->data_seek(0); } ?>
                                            </select>
                                        </div>

                                        <div class="form-group mb-2"><label>Paket</label>
                                            <select name="paket_id" class="form-control">
                                                <?php 
                                                if ($result_paket) {
                                                    $result_paket->data_seek(0);
                                                    while ($paket = $result_paket->fetch_assoc()):
                                                ?>
                                                    <option value="<?= intval($paket['id']) ?>" <?= ($row['paket_id']==$paket['id']) ? 'selected' : '' ?>><?= htmlspecialchars($paket['nama']) ?></option>
                                                <?php endwhile; $result_paket->data_seek(0); } ?>
                                            </select>
                                        </div>

                                        <div class="form-group mb-2"><label>Publish</label><input type="date" name="tanggal_publish" class="form-control" value="<?= htmlspecialchars($row['tanggal_publish']) ?>"></div>
                                        <div class="form-group mb-2"><label>Tenggat</label><input type="date" name="tenggat_waktu" class="form-control" value="<?= htmlspecialchars($row['tenggat_waktu']) ?>"></div>
                                    </div>
                                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2"><label>Judul</label><input type="text" name="judul" class="form-control" required></div>

                    <div class="form-group mb-2"><label>Kelas</label>
                        <select name="kelas_id" class="form-control" required>
                            <?php
                            if ($result_kelas) {
                                $result_kelas->data_seek(0);
                                while ($kelas = $result_kelas->fetch_assoc()):
                            ?>
                                <option value="<?= intval($kelas['id']) ?>"><?= htmlspecialchars($kelas['nama_kelas']) ?></option>
                            <?php endwhile; $result_kelas->data_seek(0); } ?>
                        </select>
                    </div>

                    <div class="form-group mb-2"><label>Pelajaran</label>
                        <select name="kategori_id" class="form-control" required>
                            <?php
                            if ($result_kategori) {
                                $result_kategori->data_seek(0);
                                while ($kategori = $result_kategori->fetch_assoc()):
                            ?>
                                <option value="<?= intval($kategori['id']) ?>"><?= htmlspecialchars($kategori['nama_kategori']) ?></option>
                            <?php endwhile; $result_kategori->data_seek(0); } ?>
                        </select>
                    </div>

                    <div class="form-group mb-2"><label>Paket</label>
                        <select name="paket_id" class="form-control" required>
                            <?php
                            if ($result_paket) {
                                $result_paket->data_seek(0);
                                while ($paket = $result_paket->fetch_assoc()):
                            ?>
                                <option value="<?= intval($paket['id']) ?>"><?= htmlspecialchars($paket['nama']) ?></option>
                            <?php endwhile; $result_paket->data_seek(0); } ?>
                        </select>
                    </div>

                    <div class="form-group mb-2"><label>Publish</label><input type="date" name="tanggal_publish" class="form-control" required></div>
                    <div class="form-group mb-2"><label>Tenggat</label><input type="date" name="tenggat_waktu" class="form-control" required></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success">Tambah</button></div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<!-- JS/CSS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#latihanTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [ [5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"] ],
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "info": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            "paginate": {
                "first": "Awal",
                "last": "Akhir",
                "next": "›",
                "previous": "‹"
            }
        }
    });
});
</script>
