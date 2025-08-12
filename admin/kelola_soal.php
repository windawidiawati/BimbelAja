<?php
// Pastikan ini adalah baris pertama file, tanpa spasi/enter sebelumnya
ob_start(); // Mulai output buffering
include '../config/database.php';
include '../includes/admin_header.php';

// Ambil data kategori_materi untuk dropdown
$kategori_result = $conn->query("SELECT * FROM kategori_materi");
$kategori_map = array();
while ($k = $kategori_result->fetch_assoc()) {
    $kategori_map[$k['id']] = $k['nama_kategori'];
}

// Ambil data kelas untuk dropdown
$kelas_result = $conn->query("SELECT * FROM kelas");
$kelas_map = array();
while ($kls = $kelas_result->fetch_assoc()) {
    $kelas_map[$kls['id']] = $kls['nama_kelas'] . ' (' . $kls['jenjang'] . ')';
}

// Filter
$jenjang = isset($_POST['jenjang']) ? $_POST['jenjang'] : '';
$mata_pelajaran = isset($_POST['mata_pelajaran']) ? $_POST['mata_pelajaran'] : '';

$query = "SELECT soal.*, kategori_materi.nama_kategori, kelas.nama_kelas, kelas.jenjang 
          FROM soal 
          JOIN kategori_materi ON soal.kategori_id = kategori_materi.id 
          JOIN kelas ON soal.kelas_id = kelas.id
          WHERE 1=1";

$params = array();
$types = '';
if ($jenjang) {
    $query .= " AND kelas.jenjang = ?";
    $types .= 's';
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

// Hapus soal
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM soal WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Soal berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus soal";
    }
    ob_end_clean(); // Bersihkan buffer sebelum redirect
    header("Location: kelola_soal.php");
    exit;
}

// Tambah soal
if (isset($_POST['tambah_soal'])) {
    // Validasi data sebelum disimpan
    if (empty($_POST['pertanyaan']) || empty($_POST['opsi_a']) || empty($_POST['opsi_b']) || 
        empty($_POST['opsi_c']) || empty($_POST['opsi_d']) || empty($_POST['jawaban']) || 
        empty($_POST['kelas_id']) || empty($_POST['kategori_id'])) {
        $_SESSION['error'] = "Semua field harus diisi!";
    } else {
        $stmt = $conn->prepare("INSERT INTO soal (pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban, tutor_id, kelas_id, kategori_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
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
        if ($stmt->execute()) {
            $_SESSION['success'] = "Soal berhasil ditambahkan";
        } else {
            $_SESSION['error'] = "Gagal menambahkan soal: " . $conn->error;
        }
    }
    ob_end_clean(); // Bersihkan buffer sebelum redirect
    header("Location: kelola_soal.php");
    exit;
}

// Edit soal
if (isset($_POST['edit_soal'])) {
    // Validasi data sebelum diupdate
    if (empty($_POST['pertanyaan']) || empty($_POST['opsi_a']) || empty($_POST['opsi_b']) || 
        empty($_POST['opsi_c']) || empty($_POST['opsi_d']) || empty($_POST['jawaban']) || 
        empty($_POST['kelas_id']) || empty($_POST['kategori_id'])) {
        $_SESSION['error'] = "Semua field harus diisi!";
    } else {
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
        if ($stmt->execute()) {
            $_SESSION['success'] = "Soal berhasil diperbarui";
        } else {
            $_SESSION['error'] = "Gagal memperbarui soal: " . $conn->error;
        }
    }
    ob_end_clean(); // Bersihkan buffer sebelum redirect
    header("Location: kelola_soal.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Soal</title>
    <!-- Tambahkan CSS atau JS tambahan jika diperlukan -->
</head>
<body>
<div class="content">
    <h2 class="mb-4">Kelola Soal</h2>

    <!-- Pesan Notifikasi -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Filter -->
    <div class="bg-white rounded shadow-sm p-4 mb-4">
        <form method="POST" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="jenjang" class="form-label">Jenjang</label>
                <select name="jenjang" class="form-select">
                    <option value="">Semua Jenjang</option>
                    <option value="SD" <?= ($jenjang == "SD") ? "selected" : "" ?>>SD</option>
                    <option value="SMP" <?= ($jenjang == "SMP") ? "selected" : "" ?>>SMP</option>
                    <option value="SMA" <?= ($jenjang == "SMA") ? "selected" : "" ?>>SMA</option>
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
    <div class="table-responsive" style="max-height: 500px; overflow: auto;">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-primary text-center">
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
                        <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
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
                            <a href="kelola_soal.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin hapus soal ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="11" class="text-center">Tidak ada data soal</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Soal -->
<div class="modal fade" id="tambahSoalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" id="formTambahSoal">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Soal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                        <textarea name="pertanyaan" class="form-control" placeholder="Masukkan pertanyaan" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Opsi A <span class="text-danger">*</span></label>
                            <input type="text" name="opsi_a" class="form-control" placeholder="Opsi A" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Opsi B <span class="text-danger">*</span></label>
                            <input type="text" name="opsi_b" class="form-control" placeholder="Opsi B" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Opsi C <span class="text-danger">*</span></label>
                            <input type="text" name="opsi_c" class="form-control" placeholder="Opsi C" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Opsi D <span class="text-danger">*</span></label>
                            <input type="text" name="opsi_d" class="form-control" placeholder="Opsi D" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jawaban Benar <span class="text-danger">*</span></label>
                            <select name="jawaban" class="form-select" required>
                                <option value="">Pilih Jawaban</option>
                                <option value="A">Opsi A</option>
                                <option value="B">Opsi B</option>
                                <option value="C">Opsi C</option>
                                <option value="D">Opsi D</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select name="kelas_id" class="form-select" required>
                                <option value="">Pilih Kelas</option>
                                <?php foreach ($kelas_map as $id => $nama): ?>
                                    <option value="<?= $id ?>"><?= htmlspecialchars($nama) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="kategori_id" class="form-select" required>
                                <option value="">Pilih Mata Pelajaran</option>
                                <?php foreach ($kategori_map as $id => $nama): ?>
                                    <option value="<?= $id ?>"><?= htmlspecialchars($nama) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="tambah_soal">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Soal -->
<div class="modal fade" id="editSoalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" id="formEditSoal">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Soal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                        <textarea name="pertanyaan" id="edit-pertanyaan" class="form-control" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Opsi A <span class="text-danger">*</span></label>
                            <input type="text" name="opsi_a" id="edit-opsi_a" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Opsi B <span class="text-danger">*</span></label>
                            <input type="text" name="opsi_b" id="edit-opsi_b" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Opsi C <span class="text-danger">*</span></label>
                            <input type="text" name="opsi_c" id="edit-opsi_c" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Opsi D <span class="text-danger">*</span></label>
                            <input type="text" name="opsi_d" id="edit-opsi_d" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jawaban Benar <span class="text-danger">*</span></label>
                            <select name="jawaban" id="edit-jawaban" class="form-select" required>
                                <option value="A">Opsi A</option>
                                <option value="B">Opsi B</option>
                                <option value="C">Opsi C</option>
                                <option value="D">Opsi D</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select name="kelas_id" id="edit-kelas_id" class="form-select" required>
                                <?php foreach ($kelas_map as $id => $nama): ?>
                                    <option value="<?= $id ?>"><?= htmlspecialchars($nama) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="kategori_id" id="edit-kategori_id" class="form-select" required>
                                <?php foreach ($kategori_map as $id => $nama): ?>
                                    <option value="<?= $id ?>"><?= htmlspecialchars($nama) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="edit_soal">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>

<script>
// Script untuk mengisi data ke modal edit
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

// Validasi form sebelum submit
document.getElementById('formTambahSoal').addEventListener('submit', function(e) {
    const inputs = this.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Harap isi semua field yang wajib diisi!');
    }
});

document.getElementById('formEditSoal').addEventListener('submit', function(e) {
    const inputs = this.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Harap isi semua field yang wajib diisi!');
    }
});
</script>
<?php ob_end_flush(); ?>