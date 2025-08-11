<?php
include '../includes/auth.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'tutor') {
    header('Location: ../index.php');
    exit;
}

$tutor_id = $_SESSION['user']['id'];

// Ambil kelas & kategori
$kelas_result = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
$kategori_result = mysqli_query($conn, "SELECT * FROM kategori_materi ORDER BY nama_kategori ASC");

// Filter daftar latihan
$filter_kelas = isset($_GET['kelas_filter']) ? (int) $_GET['kelas_filter'] : 0;
$filter_kategori = isset($_GET['kategori_filter']) ? (int) $_GET['kategori_filter'] : 0;

$where = "WHERE l.tutor_id = $tutor_id";
if ($filter_kelas > 0) $where .= " AND l.kelas_id = $filter_kelas";
if ($filter_kategori > 0) $where .= " AND l.kategori_id = $filter_kategori";

// Proses tambah latihan + soal
if (isset($_POST['simpan_latihan'])) {
    $judul_latihan = mysqli_real_escape_string($conn, $_POST['judul_latihan']);
    $kelas_id = (int) $_POST['kelas_id'];
    $kategori_id = (int) $_POST['kategori_id'];
    $durasi_menit = (int) $_POST['durasi_menit'];
    $tanggal_publish = $_POST['tanggal_publish'];
    $tenggat_waktu = $_POST['tenggat_waktu'];

    // Konversi ke format DATETIME
    $tanggal_publish = date('Y-m-d H:i:s', strtotime($tanggal_publish));
    $tenggat_waktu = date('Y-m-d H:i:s', strtotime($tenggat_waktu));
    $paket_id = $_POST['paket_id'];

    $insert_latihan = "INSERT INTO latihan (judul, tutor_id, kelas_id, kategori_id, durasi_menit, tanggal_publish, tenggat_waktu, paket_id, created_at) 
                       VALUES ('$judul_latihan', '$tutor_id', '$kelas_id', '$kategori_id', '$durasi_menit', '$tanggal_publish', '$tenggat_waktu', '$paket_id', NOW())";
    if (mysqli_query($conn, $insert_latihan)) {
        $latihan_id = mysqli_insert_id($conn);

        if (!empty($_POST['pertanyaan'])) {
            foreach ($_POST['pertanyaan'] as $i => $pertanyaan) {
                $pertanyaan = mysqli_real_escape_string($conn, $pertanyaan);
                $opsi_a = mysqli_real_escape_string($conn, $_POST['opsi_a'][$i]);
                $opsi_b = mysqli_real_escape_string($conn, $_POST['opsi_b'][$i]);
                $opsi_c = mysqli_real_escape_string($conn, $_POST['opsi_c'][$i]);
                $opsi_d = mysqli_real_escape_string($conn, $_POST['opsi_d'][$i]);
                $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban'][$i]);

                $sql = "INSERT INTO soal (latihan_id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban, tutor_id, kelas_id, kategori_id, created_at)
                        VALUES ($latihan_id, '$pertanyaan', '$opsi_a', '$opsi_b', '$opsi_c', '$opsi_d', '$jawaban', $tutor_id, $kelas_id, $kategori_id, NOW())";
                mysqli_query($conn, $sql);
            }
        }
        $success = "Latihan dan soal berhasil disimpan.";
    } else {
        $error = "Gagal menyimpan latihan.";
    }
}

// Ambil daftar latihan beserta jumlah soal
$query_latihan = "SELECT l.*, k.nama_kelas, km.nama_kategori, p.nama AS nama_paket, 
                 (SELECT COUNT(*) FROM soal s WHERE s.latihan_id = l.id) AS total_soal
                 FROM latihan l
                 LEFT JOIN kelas k ON l.kelas_id = k.id
                 LEFT JOIN kategori_materi km ON l.kategori_id = km.id
                 LEFT JOIN paket p ON l.paket_id = p.id
                 $where
                 ORDER BY l.created_at DESC";
                 
$latihan_list = mysqli_query($conn, $query_latihan);

// Ambil data kelas & kategori untuk filter tabel
$kelas_filter_result = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
$kategori_filter_result = mysqli_query($conn, "SELECT * FROM kategori_materi ORDER BY nama_kategori ASC");

include '../includes/tutor_header.php';
?>

<div class="content">
    <div class="card shadow-sm p-4 mb-5">
        <h3 class="mb-4 text-primary">📝 Tambah Latihan Baru</h3>

        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <form method="POST" id="formLatihan">
            <div class="mb-3">
                <label class="form-label fw-semibold">Judul Latihan</label>
                <input type="text" name="judul_latihan" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php while ($k = mysqli_fetch_assoc($kelas_result)): ?>
                        <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Mata Pelajaran</label>
                <select name="kategori_id" class="form-select" required>
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <?php while ($km = mysqli_fetch_assoc($kategori_result)): ?>
                        <option value="<?= $km['id'] ?>"><?= htmlspecialchars($km['nama_kategori']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label">Durasi (menit)</label>
                <input type="number" name="durasi_menit" class="form-control" value="30" required>
            </div>
             <div class="mb-3">
                <label for="tanggal_publish">Tanggal Publish</label>
                <input type="datetime-local" class="form-control" name="tanggal_publish" required>
                    </div>
            <div class="mb-3">
                <label for="tenggat_waktu">Tenggat Waktu</label>
                <input type="datetime-local" class="form-control" name="tenggat_waktu" required>
                    </div>
            <div class="mb-3">
                <label for="paket" class="form-label">Paket</label>
                <select class="form-select" name="paket_id" id="paket" required>
                    <option value="">-- Pilih Paket --</option>
                    <?php
                    $queryPaket = mysqli_query($conn, "SELECT * FROM paket");
                    while ($paket = mysqli_fetch_assoc($queryPaket)) {
                       echo '<option value="'.$paket['id'].'">'.$paket['nama'].'</option>';
                    }
                    ?>
                </select>
                    </div>

            <h5>Daftar Soal</h5>
            <div id="soalContainer">
                <div class="soal-item border rounded p-3 mb-3 position-relative">
                    <h6 class="fw-bold">Soal 1</h6>
                    <div class="mb-3">
                        <label>Pertanyaan</label>
                        <textarea name="pertanyaan[]" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2"><input type="text" name="opsi_a[]" class="form-control" placeholder="Opsi A" required></div>
                        <div class="col-md-6 mb-2"><input type="text" name="opsi_b[]" class="form-control" placeholder="Opsi B" required></div>
                        <div class="col-md-6 mb-2"><input type="text" name="opsi_c[]" class="form-control" placeholder="Opsi C" required></div>
                        <div class="col-md-6 mb-2"><input type="text" name="opsi_d[]" class="form-control" placeholder="Opsi D" required></div>
                    </div>
                    <div class="mb-2">
                        <label>Jawaban Benar</label>
                        <select name="jawaban[]" class="form-select" required>
                            <option value="">-- Pilih Jawaban --</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary mb-3" onclick="tambahSoal()">+ Tambah Soal</button>

            <button type="submit" name="simpan_latihan" class="btn btn-success w-100">💾 Simpan Latihan</button>
        </form>
    </div>

    <div>
        <h4 class="mb-3">📚 Daftar Latihan</h4>
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <select name="kelas_filter" class="form-select">
                    <option value="0">-- Semua Kelas --</option>
                    <?php while ($kf = mysqli_fetch_assoc($kelas_filter_result)): ?>
                        <option value="<?= $kf['id'] ?>" <?= $filter_kelas==$kf['id']?'selected':'' ?>><?= htmlspecialchars($kf['nama_kelas']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select name="kategori_filter" class="form-select">
                    <option value="0">-- Semua Pelajaran --</option>
                    <?php while ($kf = mysqli_fetch_assoc($kategori_filter_result)): ?>
                        <option value="<?= $kf['id'] ?>" <?= $filter_kategori==$kf['id']?'selected':'' ?>><?= htmlspecialchars($kf['nama_kategori']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Notes -->
<div class="alert alert-warning mb-3" role="alert">
    <strong>Catatan:</strong> Latihan yang dibuat akan otomatis muncul di <em>Latihan Siswa</em> sesuai dengan tanggal publish yang sudah ditentukan.
</div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Kelas</th>
                        <th>Pelajaran</th>
                        <th>Durasi (menit)</th>
                        <th>Total Soal</th>
                        <th>Dibuat</th>
                        <th>Tanggal Publish</th>
                        <th>Tenggat Waktu</th>
                        <th>Paket</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; while ($row = mysqli_fetch_assoc($latihan_list)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['judul']) ?></td>
                            <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                            <td><?= $row['durasi_menit'] ?></td>
                            <td><?= $row['total_soal'] ?></td>
                            <td><?= $row['created_at'] ?></td>
                            <td><?= $row['tanggal_publish'] ?></td>
                            <td><?= $row['tenggat_waktu'] ?></td>
                            <td><?= htmlspecialchars($row['nama_paket'] ?? '') ?></td>
                            <td><a href="lihat_soal.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">👁 Lihat Soal</a></td>

                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let soalCount = 1;
function tambahSoal() {
    soalCount++;
    const container = document.getElementById('soalContainer');
    const soalItem = document.createElement('div');
    soalItem.className = 'soal-item border rounded p-3 mb-3 position-relative';
    soalItem.innerHTML = `
        <button type="button" class="btn btn-sm btn-danger position-absolute" style="top:10px; right:10px;" onclick="hapusSoal(this)">Hapus</button>
        <h6 class="fw-bold">Soal ${soalCount}</h6>
        <div class="mb-3">
            <label>Pertanyaan</label>
            <textarea name="pertanyaan[]" class="form-control" rows="2" required></textarea>
        </div>
        <div class="row">
            <div class="col-md-6 mb-2"><input type="text" name="opsi_a[]" class="form-control" placeholder="Opsi A" required></div>
            <div class="col-md-6 mb-2"><input type="text" name="opsi_b[]" class="form-control" placeholder="Opsi B" required></div>
            <div class="col-md-6 mb-2"><input type="text" name="opsi_c[]" class="form-control" placeholder="Opsi C" required></div>
            <div class="col-md-6 mb-2"><input type="text" name="opsi_d[]" class="form-control" placeholder="Opsi D" required></div>
        </div>
        <div class="mb-2">
            <label>Jawaban Benar</label>
            <select name="jawaban[]" class="form-select" required>
                <option value="">-- Pilih Jawaban --</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>
        </div>
    `;
    container.appendChild(soalItem);
}

function hapusSoal(btn) {
    btn.parentElement.remove();
}
</script>

<?php include '../includes/tutor_footer.php'; ?>
