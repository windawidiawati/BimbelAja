<?php
include '../config/database.php'; // Menghubungkan ke database
include '../includes/admin_header.php'; // Menggunakan header admin

// Ambil data soal dari database dengan filter
$jenjang = isset($_POST['jenjang']) ? $_POST['jenjang'] : '';
$mata_pelajaran = isset($_POST['mata_pelajaran']) ? $_POST['mata_pelajaran'] : '';

$query = "SELECT * FROM soal WHERE 1=1"; // 1=1 untuk memudahkan penambahan kondisi
if ($jenjang) {
    $query .= " AND kelas_id = ?";
}
if ($mata_pelajaran) {
    $query .= " AND kategori_id = ?";
}

$stmt = $conn->prepare($query);
if ($jenjang && $mata_pelajaran) {
    $stmt->bind_param("ii", $jenjang, $mata_pelajaran);
} elseif ($jenjang) {
    $stmt->bind_param("i", $jenjang);
} elseif ($mata_pelajaran) {
    $stmt->bind_param("i", $mata_pelajaran);
}
$stmt->execute();
$result = $stmt->get_result();

// Proses hapus soal
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM soal WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: kelola_soal.php");
    exit;
}

// Proses tambah soal
if (isset($_POST['tambah_soal'])) {
    $pertanyaan = $_POST['pertanyaan'];
    $opsi_a = $_POST['opsi_a'];
    $opsi_b = $_POST['opsi_b'];
    $opsi_c = $_POST['opsi_c'];
    $opsi_d = $_POST['opsi_d'];
    $jawaban = $_POST['jawaban'];
    $tutor_id = $_SESSION['user']['id']; // Ambil tutor_id dari session
    $kelas_id = $_POST['kelas_id'];
    $kategori_id = $_POST['kategori_id'];

    $stmt = $conn->prepare("INSERT INTO soal (pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban, tutor_id, kelas_id, kategori_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssiii", $pertanyaan, $opsi_a, $opsi_b, $opsi_c, $opsi_d, $jawaban, $tutor_id, $kelas_id, $kategori_id);
    $stmt->execute();
    $stmt->close();

    header("Location: kelola_soal.php");
    exit;
}

// Proses edit soal
if (isset($_POST['edit_soal'])) {
    $id = $_POST['id'];
    $pertanyaan = $_POST['pertanyaan'];
    $opsi_a = $_POST['opsi_a'];
    $opsi_b = $_POST['opsi_b'];
    $opsi_c = $_POST['opsi_c'];
    $opsi_d = $_POST['opsi_d'];
    $jawaban = $_POST['jawaban'];
    $kelas_id = $_POST['kelas_id'];
    $kategori_id = $_POST['kategori_id'];

    $stmt = $conn->prepare("UPDATE soal SET pertanyaan = ?, opsi_a = ?, opsi_b = ?, opsi_c = ?, opsi_d = ?, jawaban = ?, kelas_id = ?, kategori_id = ? WHERE id = ?");
    $stmt->bind_param("ssssssiiii", $pertanyaan, $opsi_a, $opsi_b, $opsi_c, $opsi_d, $jawaban, $kelas_id, $kategori_id, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: kelola_soal.php");
    exit;
}
?>

<div class="content">
    <h2 class="mb-4">Kelola Soal</h2>

    <!-- Filter Section -->
    <div class="bg-white rounded shadow-sm p-4 mb-4">
    <form method="POST" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label for="jenjang" class="form-label">Jenjang</label>
            <select name="jenjang" id="jenjang" class="form-select">
                <option value="">Semua Jenjang</option>
                <option value="1" <?= ($jenjang == "1") ? "selected" : "" ?>>SD</option>
                <option value="2" <?= ($jenjang == "2") ? "selected" : "" ?>>SMP</option>
                <option value="3" <?= ($jenjang == "3") ? "selected" : "" ?>>SMA</option>
            </select>
        </div>
        <div class="col-md-4">
            <label for="mata_pelajaran" class="form-label">Mata Pelajaran</label>
            <select name="mata_pelajaran" id="mata_pelajaran" class="form-select">
                <option value="">Mata Pelajaran</option>
                <option value="1" <?= ($mata_pelajaran == "1") ? "selected" : "" ?>>Matematika</option>
                <option value="2" <?= ($mata_pelajaran == "2") ? "selected" : "" ?>>Fisika</option>
                <option value="3" <?= ($mata_pelajaran == "3") ? "selected" : "" ?>>Kimia</option>
                <option value="4" <?= ($mata_pelajaran == "4") ? "selected" : "" ?>>Biologi</option>
            </select>
</div>
            <div class="col-md-4 text-end">
    <button type="submit" class="btn btn-primary px-4">Filter</button>
</div>

        </form>
    </div>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#tambahSoalModal">Tambah Soal</button>

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
                <th>Kelas ID</th>
                <th>Kategori ID</th>
                <th>Tanggal Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['pertanyaan']) ?></td>
                        <td><?= htmlspecialchars($row['opsi_a']) ?></td>
                        <td><?= htmlspecialchars($row['opsi_b']) ?></td>
                        <td><?= htmlspecialchars($row['opsi_c']) ?></td>
                        <td><?= htmlspecialchars($row['opsi_d']) ?></td>
                        <td><?= htmlspecialchars($row['jawaban']) ?></td>
                        <td><?= htmlspecialchars($row['kelas_id']) ?></td>
                        <td><?= htmlspecialchars($row['kategori_id']) ?></td>
                        <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editSoalModal" data-id="<?= $row['id'] ?>" data-pertanyaan="<?= htmlspecialchars($row['pertanyaan']) ?>" data-opsi_a="<?= htmlspecialchars($row['opsi_a']) ?>" data-opsi_b="<?= htmlspecialchars($row['opsi_b']) ?>" data-opsi_c="<?= htmlspecialchars($row['opsi_c']) ?>" data-opsi_d="<?= htmlspecialchars($row['opsi_d']) ?>" data-jawaban="<?= htmlspecialchars($row['jawaban']) ?>" data-kelas_id="<?= $row['kelas_id'] ?>" data-kategori_id="<?= $row['kategori_id'] ?>">Edit</button>
                            <a href="?action=delete&id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus soal ini?')" class="btn btn-danger btn-sm">Hapus</a>
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
<div class="modal fade" id="tambahSoalModal" tabindex="-1" aria-labelledby="tambahSoalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahSoalModalLabel">Tambah Soal Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="pertanyaan" class="form-label">Pertanyaan</label>
                        <textarea name="pertanyaan" id="pertanyaan" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="opsi_a" class="form-label">Opsi A</label>
                        <input type="text" name="opsi_a" id="opsi_a" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="opsi_b" class="form-label">Opsi B</label>
                        <input type="text" name="opsi_b" id="opsi_b" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="opsi_c" class="form-label">Opsi C</label>
                        <input type="text" name="opsi_c" id="opsi_c" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="opsi_d" class="form-label">Opsi D</label>
                        <input type="text" name="opsi_d" id="opsi_d" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="jawaban" class="form-label">Jawaban</label>
                        <select name="jawaban" id="jawaban" class="form-select" required>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="kelas_id" class="form-label">Kelas ID</label>
                        <input type="number" name="kelas_id" id="kelas_id" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="kategori_id" class="form-label">Kategori ID</label>
                        <input type="number" name="kategori_id" id="kategori_id" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_soal" class="btn btn-primary">Simpan Soal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Soal -->
<div class="modal fade" id="editSoalModal" tabindex="-1" aria-labelledby="editSoalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSoalModalLabel">Edit Soal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label for="edit-pertanyaan" class="form-label">Pertanyaan</label>
                        <textarea name="pertanyaan" id="edit-pertanyaan" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-opsi_a" class="form-label">Opsi A</label>
                        <input type="text" name="opsi_a" id="edit-opsi_a" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-opsi_b" class="form-label">Opsi B</label>
                        <input type="text" name="opsi_b" id="edit-opsi_b" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-opsi_c" class="form-label">Opsi C</label>
                        <input type="text" name="opsi_c" id="edit-opsi_c" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-opsi_d" class="form-label">Opsi D</label>
                        <input type="text" name="opsi_d" id="edit-opsi_d" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-jawaban" class="form-label">Jawaban</label>
                        <select name="jawaban" id="edit-jawaban" class="form-select" required>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-kelas_id" class="form-label">Kelas ID</label>
                        <input type="number" name="kelas_id" id="edit-kelas_id" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-kategori_id" class="form-label">Kategori ID</label>
                        <input type="number" name="kategori_id" id="edit-kategori_id" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_soal" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/admin_footer.php'; // Menggunakan footer admin ?>


<script>
    // Script untuk mengisi data ke modal edit
    const editSoalModal = document.getElementById('editSoalModal');
    editSoalModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget; // Button yang memicu modal
        const id = button.getAttribute('data-id');
        const pertanyaan = button.getAttribute('data-pertanyaan');
        const opsi_a = button.getAttribute('data-opsi_a');
        const opsi_b = button.getAttribute('data-opsi_b');
        const opsi_c = button.getAttribute('data-opsi_c');
        const opsi_d = button.getAttribute('data-opsi_d');
        const jawaban = button.getAttribute('data-jawaban');
        const kelas_id = button.getAttribute('data-kelas_id');
        const kategori_id = button.getAttribute('data-kategori_id');

        // Mengisi data ke modal
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-pertanyaan').value = pertanyaan;
        document.getElementById('edit-opsi_a').value = opsi_a;
        document.getElementById('edit-opsi_b').value = opsi_b;
        document.getElementById('edit-opsi_c').value = opsi_c;
        document.getElementById('edit-opsi_d').value = opsi_d;
        document.getElementById('edit-jawaban').value = jawaban;
        document.getElementById('edit-kelas_id').value = kelas_id;
        document.getElementById('edit-kategori_id').value = kategori_id;
    });
</script>
