<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'tutor') {
    header('Location: ../index.php');
    exit;
}
include '../config/database.php';

$tutor_id = $_SESSION['user']['id'];

// Validasi ID latihan
if (!isset($_GET['id'])) {
    header('Location: buat_soal.php');
    exit;
}
$latihan_id = (int) $_GET['id'];

/* =====================
   PROSES ACTION
===================== */

// Hapus soal
if (isset($_GET['hapus_soal'])) {
    $soal_id = (int) $_GET['hapus_soal'];
    mysqli_query($conn, "DELETE FROM soal WHERE id = $soal_id AND latihan_id = $latihan_id AND tutor_id = $tutor_id");
    header("Location: lihat_soal.php?id=$latihan_id");
    exit;
}

// Hapus latihan (beserta semua soalnya)
if (isset($_GET['hapus_latihan'])) {
    mysqli_query($conn, "DELETE FROM soal WHERE latihan_id = $latihan_id");
    mysqli_query($conn, "DELETE FROM latihan WHERE id = $latihan_id AND tutor_id = $tutor_id");
    header("Location: buat_soal.php");
    exit;
}

// Update info latihan
if (isset($_POST['update_latihan'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $durasi = (int) $_POST['durasi_menit'];
    $tgl_publish = mysqli_real_escape_string($conn, $_POST['tanggal_publish']);
    $tenggat = mysqli_real_escape_string($conn, $_POST['tenggat_waktu']);

    mysqli_query($conn, "UPDATE latihan SET 
        judul = '$judul',
        durasi_menit = $durasi,
        tanggal_publish = '$tgl_publish',
        tenggat_waktu = '$tenggat'
        WHERE id = $latihan_id AND tutor_id = $tutor_id");

    header("Location: lihat_soal.php?id=$latihan_id");
    exit;
}

// Tambah soal
if (isset($_POST['tambah_soal'])) {
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $a = mysqli_real_escape_string($conn, $_POST['opsi_a']);
    $b = mysqli_real_escape_string($conn, $_POST['opsi_b']);
    $c = mysqli_real_escape_string($conn, $_POST['opsi_c']);
    $d = mysqli_real_escape_string($conn, $_POST['opsi_d']);
    $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban']);

    mysqli_query($conn, "INSERT INTO soal (latihan_id, tutor_id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban) 
        VALUES ($latihan_id, $tutor_id, '$pertanyaan', '$a', '$b', '$c', '$d', '$jawaban')");

    header("Location: lihat_soal.php?id=$latihan_id");
    exit;
}

// Update soal
if (isset($_POST['update_soal'])) {
    $soal_id = (int) $_POST['soal_id'];
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $a = mysqli_real_escape_string($conn, $_POST['opsi_a']);
    $b = mysqli_real_escape_string($conn, $_POST['opsi_b']);
    $c = mysqli_real_escape_string($conn, $_POST['opsi_c']);
    $d = mysqli_real_escape_string($conn, $_POST['opsi_d']);
    $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban']);

    mysqli_query($conn, "UPDATE soal SET 
        pertanyaan = '$pertanyaan',
        opsi_a = '$a',
        opsi_b = '$b',
        opsi_c = '$c',
        opsi_d = '$d',
        jawaban = '$jawaban'
        WHERE id = $soal_id AND latihan_id = $latihan_id AND tutor_id = $tutor_id");

    header("Location: lihat_soal.php?id=$latihan_id");
    exit;
}

/* =====================
   AMBIL DATA
===================== */
$query = "SELECT l.*, k.nama_kelas, km.nama_kategori
          FROM latihan l
          LEFT JOIN kelas k ON l.kelas_id = k.id
          LEFT JOIN kategori_materi km ON l.kategori_id = km.id
          WHERE l.id = $latihan_id AND l.tutor_id = $tutor_id";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    include '../includes/tutor_header.php';
    echo "<div class='alert alert-danger'>Latihan tidak ditemukan.</div>";
    include '../includes/tutor_footer.php';
    exit;
}

$filter = '';
$keyword = '';
if (!empty($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($conn, $_GET['cari']);
    $filter = "AND s.pertanyaan LIKE '%$keyword%'";
}

$query_soal = "SELECT * FROM soal s WHERE s.latihan_id = $latihan_id $filter ORDER BY s.id ASC";
$soal = mysqli_query($conn, $query_soal);

$total_soal = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM soal WHERE latihan_id = $latihan_id"));

/* =====================
   TAMPILAN
===================== */
include '../includes/tutor_header.php';
?>

<div class="content">
    <div class="container py-4">
        <div class="card shadow-sm p-4 mb-4">
            <h3 class="text-primary mb-3">👁 Lihat Soal</h3>

            <!-- Info Latihan -->
            <div class="p-3 mb-3" style="background-color:#e9f3ff; border-radius:8px;">
                <p><b>Judul Latihan:</b> <?= htmlspecialchars($data['judul']) ?></p>
                <p><b>Kelas:</b> <?= htmlspecialchars($data['nama_kelas']) ?></p>
                <p><b>Mata Pelajaran:</b> <?= htmlspecialchars($data['nama_kategori']) ?></p>
                <p><b>Durasi:</b> <?= $data['durasi_menit'] ?> menit</p>
                <p><b>Tanggal Publish:</b> <?= date('d-m-Y H:i', strtotime($data['tanggal_publish'])) ?></p>
                <p><b>Tenggat Waktu:</b> <?= date('d-m-Y H:i', strtotime($data['tenggat_waktu'])) ?></p>
                <p><b>Total Soal:</b> <?= $total_soal ?></p>
            </div>

            <!-- Tombol Edit & Hapus Latihan -->
            <a href="?id=<?= $latihan_id ?>&hapus_latihan=1" class="btn btn-danger mb-3" onclick="return confirm('Hapus latihan ini beserta semua soalnya?')">🗑 Hapus Latihan</a>
            <button class="btn btn-warning mb-3" type="button" onclick="document.getElementById('formEditLatihan').style.display='block'">✏ Edit Latihan</button>

            <!-- Form Edit Latihan -->
            <div id="formEditLatihan" style="display:none; background:#fff3cd; padding:15px; border-radius:8px;">
                <form method="post">
                    <input type="hidden" name="update_latihan" value="1">
                    <div class="mb-2">
                        <label>Judul Latihan</label>
                        <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($data['judul']) ?>" required>
                    </div>
                    <div class="mb-2">
                        <label>Durasi (menit)</label>
                        <input type="number" name="durasi_menit" class="form-control" value="<?= $data['durasi_menit'] ?>" required>
                    </div>
                    <div class="mb-2">
                        <label>Tanggal Publish</label>
                        <input type="datetime-local" name="tanggal_publish" class="form-control" 
                               value="<?= date('Y-m-d\TH:i', strtotime($data['tanggal_publish'])) ?>" required>
                    </div>
                    <div class="mb-2">
                        <label>Tenggat Waktu</label>
                        <input type="datetime-local" name="tenggat_waktu" class="form-control" 
                               value="<?= date('Y-m-d\TH:i', strtotime($data['tenggat_waktu'])) ?>" required>
                    </div>
                    <button class="btn btn-primary">💾 Simpan Perubahan</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('formEditLatihan').style.display='none'">Batal</button>
                </form>
            </div>

            <!-- Form Pencarian -->
            <form method="get" class="mb-3 mt-3">
                <input type="hidden" name="id" value="<?= $latihan_id ?>">
                <div class="input-group">
                    <input type="text" name="cari" class="form-control" placeholder="Cari pertanyaan..." value="<?= htmlspecialchars($keyword) ?>">
                    <button class="btn btn-primary">Cari</button>
                </div>
            </form>

            <!-- Tabel Soal -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>No</th>
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
                        <?php $no=1; while ($row = mysqli_fetch_assoc($soal)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['pertanyaan']) ?></td>
                                <td><?= htmlspecialchars($row['opsi_a']) ?></td>
                                <td><?= htmlspecialchars($row['opsi_b']) ?></td>
                                <td><?= htmlspecialchars($row['opsi_c']) ?></td>
                                <td><?= htmlspecialchars($row['opsi_d']) ?></td>
                                <td><strong><?= strtoupper($row['jawaban']) ?></strong></td>
                                <td>
                                    <a href="?id=<?= $latihan_id ?>&hapus_soal=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus soal ini?')">🗑 Hapus</a>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="editSoal(<?= $row['id'] ?>,'<?= htmlspecialchars($row['pertanyaan'],ENT_QUOTES) ?>','<?= htmlspecialchars($row['opsi_a'],ENT_QUOTES) ?>','<?= htmlspecialchars($row['opsi_b'],ENT_QUOTES) ?>','<?= htmlspecialchars($row['opsi_c'],ENT_QUOTES) ?>','<?= htmlspecialchars($row['opsi_d'],ENT_QUOTES) ?>','<?= $row['jawaban'] ?>')">✏ Edit</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (mysqli_num_rows($soal) === 0): ?>
                            <tr><td colspan="8" class="text-center">Tidak ada soal.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Form Tambah Soal -->
            <h5 class="mt-4">➕ Tambah Soal</h5>
            <form method="post">
                <input type="hidden" name="tambah_soal" value="1">
                <div class="mb-2">
                    <label>Pertanyaan</label>
                    <textarea name="pertanyaan" class="form-control" required></textarea>
                </div>
                <div class="mb-2"><label>A</label><input type="text" name="opsi_a" class="form-control" required></div>
                <div class="mb-2"><label>B</label><input type="text" name="opsi_b" class="form-control" required></div>
                <div class="mb-2"><label>C</label><input type="text" name="opsi_c" class="form-control" required></div>
                <div class="mb-2"><label>D</label><input type="text" name="opsi_d" class="form-control" required></div>
                <div class="mb-2"><label>Jawaban Benar</label>
                    <select name="jawaban" class="form-control" required>
                        <option value="a">A</option>
                        <option value="b">B</option>
                        <option value="c">C</option>
                        <option value="d">D</option>
                    </select>
                </div>
                <button class="btn btn-success">💾 Simpan Soal</button>
            </form>

            <!-- Form Edit Soal (Hidden) -->
            <div id="formEditSoal" style="display:none; margin-top:20px;">
                <h5>✏ Edit Soal</h5>
                <form method="post">
                    <input type="hidden" name="update_soal" value="1">
                    <input type="hidden" name="soal_id" id="edit_soal_id">
                    <div class="mb-2"><label>Pertanyaan</label><textarea name="pertanyaan" id="edit_pertanyaan" class="form-control" required></textarea></div>
                    <div class="mb-2"><label>A</label><input type="text" name="opsi_a" id="edit_opsi_a" class="form-control" required></div>
                    <div class="mb-2"><label>B</label><input type="text" name="opsi_b" id="edit_opsi_b" class="form-control" required></div>
                    <div class="mb-2"><label>C</label><input type="text" name="opsi_c" id="edit_opsi_c" class="form-control" required></div>
                    <div class="mb-2"><label>D</label><input type="text" name="opsi_d" id="edit_opsi_d" class="form-control" required></div>
                    <div class="mb-2"><label>Jawaban Benar</label>
                        <select name="jawaban" id="edit_jawaban" class="form-control" required>
                            <option value="a">A</option>
                            <option value="b">B</option>
                            <option value="c">C</option>
                            <option value="d">D</option>
                        </select>
                    </div>
                    <button class="btn btn-primary">💾 Simpan Perubahan</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('formEditSoal').style.display='none'">Batal</button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
function editSoal(id, pertanyaan, a, b, c, d, jawaban) {
    document.getElementById('formEditSoal').style.display = 'block';
    document.getElementById('edit_soal_id').value = id;
    document.getElementById('edit_pertanyaan').value = pertanyaan;
    document.getElementById('edit_opsi_a').value = a;
    document.getElementById('edit_opsi_b').value = b;
    document.getElementById('edit_opsi_c').value = c;
    document.getElementById('edit_opsi_d').value = d;
    document.getElementById('edit_jawaban').value = jawaban.toLowerCase();
    window.scrollTo(0, document.getElementById('formEditSoal').offsetTop);
}
</script>

<?php include '../includes/tutor_footer.php'; ?>
