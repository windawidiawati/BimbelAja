<?php
session_start();
include '../config/database.php';

// Tambah soal
if (isset($_POST['tambah_soal'])) {
    $stmt = $conn->prepare("INSERT INTO soal (pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban, tutor_id, kelas_id, kategori_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssiii",
        $_POST['pertanyaan'],
        $_POST['opsi_a'],
        $_POST['opsi_b'],
        $_POST['opsi_c'],
        $_POST['opsi_d'],
        $_POST['jawaban'],
        $_SESSION['user']['id'],
        $_POST['kelas_id'],
        $_POST['kategori_id']
    );
    $stmt->execute();
    header("Location: kelola_soal.php");
    exit;
}

// Edit soal
if (isset($_POST['edit_soal'])) {
    $stmt = $conn->prepare("UPDATE soal SET pertanyaan = ?, opsi_a = ?, opsi_b = ?, opsi_c = ?, opsi_d = ?, jawaban = ?, kelas_id = ?, kategori_id = ? WHERE id = ?");
    $stmt->bind_param("ssssssiii",
        $_POST['pertanyaan'],
        $_POST['opsi_a'],
        $_POST['opsi_b'],
        $_POST['opsi_c'],
        $_POST['opsi_d'],
        $_POST['jawaban'],
        $_POST['kelas_id'],
        $_POST['kategori_id'],
        $_POST['id']
    );
    $stmt->execute();
    header("Location: kelola_soal.php");
    exit;
}

// Hapus soal
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM soal WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: kelola_soal.php");
    exit;
}

include '../includes/admin_header.php';

// Ambil data kategori_materi untuk dropdown
$kategori_result = $conn->query("SELECT * FROM kategori_materi");
$kategori_map = [];
while ($k = $kategori_result->fetch_assoc()) {
    $kategori_map[$k['id']] = $k['nama_kategori'];
}

// Filter
$jenjang = isset($_POST['jenjang']) ? $_POST['jenjang'] : '';
$mata_pelajaran = isset($_POST['mata_pelajaran']) ? $_POST['mata_pelajaran'] : '';

$query = "SELECT soal.*, kategori_materi.nama_kategori 
          FROM soal 
          JOIN kategori_materi ON soal.kategori_id = kategori_materi.id 
          WHERE 1=1";

$params = [];
$types = '';
if ($jenjang) {
    $query .= " AND soal.kelas_id = ?";
    $types .= 'i';
    $params[] = $jenjang;
}
if ($mata_pelajaran) {
    $query .= " AND soal.kategori_id = ?";
    $types .= 'i';
    $params[] = $mata_pelajaran;
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="content">
    <h2 class="mb-4">Kelola Soal</h2>

    <!-- Filter -->
    <div class="bg-white rounded shadow-sm p-4 mb-4">
        <form method="POST" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="jenjang" class="form-label">Jenjang</label>
                <select name="jenjang" class="form-select">
                    <option value="">Semua Jenjang</option>
                    <option value="1" <?= ($jenjang == "1") ? "selected" : "" ?>>SD</option>
                    <option value="2" <?= ($jenjang == "2") ? "selected" : "" ?>>SMP</option>
                    <option value="3" <?= ($jenjang == "3") ? "selected" : "" ?>>SMA</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="mata_pelajaran" class="form-label">Mata Pelajaran</label>
                <select name="mata_pelajaran" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach ($kategori_map as $id => $nama): ?>
                        <option value="<?= $id ?>" <?= ($mata_pelajaran == $id) ? "selected" : "" ?>>
                            <?= htmlspecialchars($nama) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 text-end">
                <button type="submit" class="btn btn-primary px-4">Filter</button>
            </div>
        </form>
    </div>

    <!-- Button Tambah -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#tambahSoalModal">Tambah Soal</button>

    <!-- Tabel -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Pertanyaan</th>
                <th>Opsi A</th>
                <th>Opsi B</th>
                <th>Opsi C</th>
                <th>Opsi D</th>
                <th>Jawaban</th>
                <th>Kelas</th>
                <th>Mata Pelajaran</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['pertanyaan']) ?></td>
                    <td><?= htmlspecialchars($row['opsi_a']) ?></td>
                    <td><?= htmlspecialchars($row['opsi_b']) ?></td>
                    <td><?= htmlspecialchars($row['opsi_c']) ?></td>
                    <td><?= htmlspecialchars($row['opsi_d']) ?></td>
                    <td><?= htmlspecialchars($row['jawaban']) ?></td>
                    <td><?= $row['kelas_id'] ?></td>
                    <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                    <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editSoalModal"
                            data-id="<?= $row['id'] ?>"
                            data-pertanyaan="<?= htmlspecialchars($row['pertanyaan']) ?>"
                            data-opsi_a="<?= htmlspecialchars($row['opsi_a']) ?>"
                            data-opsi_b="<?= htmlspecialchars($row['opsi_b']) ?>"
                            data-opsi_c="<?= htmlspecialchars($row['opsi_c']) ?>"
                            data-opsi_d="<?= htmlspecialchars($row['opsi_d']) ?>"
                            data-jawaban="<?= $row['jawaban'] ?>"
                            data-kelas_id="<?= $row['kelas_id'] ?>"
                            data-kategori_id="<?= $row['kategori_id'] ?>">
                            Edit
                        </button>
                        <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="11" class="text-center">Tidak ada data soal</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah Soal -->
<div class="modal fade" id="tambahSoalModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header"><h5>Tambah Soal</h5></div>
                <div class="modal-body">
                    <textarea name="pertanyaan" class="form-control mb-2" placeholder="Pertanyaan" required></textarea>
                    <input name="opsi_a" class="form-control mb-2" placeholder="Opsi A" required>
                    <input name="opsi_b" class="form-control mb-2" placeholder="Opsi B" required>
                    <input name="opsi_c" class="form-control mb-2" placeholder="Opsi C" required>
                    <input name="opsi_d" class="form-control mb-2" placeholder="Opsi D" required>
                    <select name="jawaban" class="form-select mb-2">
                        <option value="A">Jawaban A</option>
                        <option value="B">Jawaban B</option>
                        <option value="C">Jawaban C</option>
                        <option value="D">Jawaban D</option>
                    </select>
                    <input type="number" name="kelas_id" class="form-control mb-2" placeholder="Kelas ID" required>
                    <select name="kategori_id" class="form-select mb-2" required>
                        <option value="">Pilih Mata Pelajaran</option>
                        <?php foreach ($kategori_map as $id => $nama): ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($nama) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit" name="tambah_soal">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editSoalModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header"><h5>Edit Soal</h5></div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <textarea name="pertanyaan" id="edit-pertanyaan" class="form-control mb-2" required></textarea>
                    <input name="opsi_a" id="edit-opsi_a" class="form-control mb-2" required>
                    <input name="opsi_b" id="edit-opsi_b" class="form-control mb-2" required>
                    <input name="opsi_c" id="edit-opsi_c" class="form-control mb-2" required>
                    <input name="opsi_d" id="edit-opsi_d" class="form-control mb-2" required>
                    <select name="jawaban" id="edit-jawaban" class="form-select mb-2">
                        <option value="A">Jawaban A</option>
                        <option value="B">Jawaban B</option>
                        <option value="C">Jawaban C</option>
                        <option value="D">Jawaban D</option>
                    </select>
                    <input type="number" name="kelas_id" id="edit-kelas_id" class="form-control mb-2" required>
                    <select name="kategori_id" id="edit-kategori_id" class="form-select mb-2" required>
                        <?php foreach ($kategori_map as $id => $nama): ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($nama) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit" name="edit_soal">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>

<script>
document.getElementById('editSoalModal').addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    document.getElementById('edit-id').value = btn.getAttribute('data-id');
    document.getElementById('edit-pertanyaan').value = btn.getAttribute('data-pertanyaan');
    document.getElementById('edit-opsi_a').value = btn.getAttribute('data-opsi_a');
    document.getElementById('edit-opsi_b').value = btn.getAttribute('data-opsi_b');
    document.getElementById('edit-opsi_c').value = btn.getAttribute('data-opsi_c');
    document.getElementById('edit-opsi_d').value = btn.getAttribute('data-opsi_d');
    document.getElementById('edit-jawaban').value = btn.getAttribute('data-jawaban');
    document.getElementById('edit-kelas_id').value = btn.getAttribute('data-kelas_id');
    document.getElementById('edit-kategori_id').value = btn.getAttribute('data-kategori_id');
});
</script>
