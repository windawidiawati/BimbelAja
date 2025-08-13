<?php
include '../includes/auth.php';
include '../includes/tutor_header.php';
include '../config/database.php';

// Cek role tutor
if ($_SESSION['user']['role'] !== 'tutor') {
    header('Location: ../index.php');
    exit;
}

$tutor_id = $_SESSION['user']['id'];

// Filter kelas & paket
$kelas_id = isset($_GET['kelas_id']) ? $_GET['kelas_id'] : '';
$paket_id = isset($_GET['paket_id']) ? $_GET['paket_id'] : '';
$pesan = "";

// Simpan absensi (jika form disubmit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jadwal_id'])) {
    $jadwal_id = $_POST['jadwal_id'];
    $tanggal = $_POST['tanggal'];
    $status = $_POST['status'];
    $keterangan = $_POST['keterangan'];

    // Cek absensi ganda
    $cek = mysqli_query($conn, "SELECT * FROM absensi_tutor WHERE jadwal_id='$jadwal_id' AND tanggal='$tanggal'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "<div class='alert alert-warning'>Absensi untuk tanggal ini sudah diisi.</div>";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO absensi_tutor (tutor_id, jadwal_id, tanggal, status, keterangan) 
                                       VALUES ('$tutor_id', '$jadwal_id', '$tanggal', '$status', '$keterangan')");
        if ($insert) {
            $pesan = "<div class='alert alert-success'>Absensi berhasil disimpan.</div>";
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal menyimpan absensi.</div>";
        }
    }
}

// Dropdown kelas & paket
$kelas_query = mysqli_query($conn, "SELECT * FROM kelas");
$paket_query = mysqli_query($conn, "SELECT * FROM paket");

// Query jadwal tutor
$query = "SELECT j.*, k.nama_kelas, p.nama_paket, m.nama_mapel 
          FROM jadwal_offline j
          LEFT JOIN kelas k ON j.kelas_id = k.id
          LEFT JOIN paket p ON j.paket_id = p.id
          LEFT JOIN mapel m ON j.mapel_id = m.id
          WHERE j.tutor_id = '$tutor_id'";

if (!empty($kelas_id)) $query .= " AND j.kelas_id = '$kelas_id'";
if (!empty($paket_id)) $query .= " AND j.paket_id = '$paket_id'";

$query .= " ORDER BY j.tanggal, j.jam_mulai";
$jadwal_result = mysqli_query($conn, $query);
?>

<div class="container mt-4">
    <h2>Absensi Tutor</h2>
    <?= $pesan ?>

    <!-- Filter -->
    <form method="GET" class="row mb-3">
        <div class="col-md-4">
            <label>Kelas</label>
            <select name="kelas_id" class="form-control">
                <option value="">Semua</option>
                <?php while ($row = mysqli_fetch_assoc($kelas_query)) { ?>
                    <option value="<?= $row['id'] ?>" <?= ($row['id'] == $kelas_id) ? 'selected' : '' ?>>
                        <?= $row['nama_kelas'] ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-4">
            <label>Paket</label>
            <select name="paket_id" class="form-control">
                <option value="">Semua</option>
                <?php while ($row = mysqli_fetch_assoc($paket_query)) { ?>
                    <option value="<?= $row['id'] ?>" <?= ($row['id'] == $paket_id) ? 'selected' : '' ?>>
                        <?= $row['nama_paket'] ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-4 align-self-end">
            <button class="btn btn-primary">Tampilkan</button>
        </div>
    </form>

    <!-- Daftar Jadwal -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Kelas</th>
                    <th>Paket</th>
                    <th>Mata Pelajaran</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($jadwal_result) > 0) {
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($jadwal_result)) {
                        echo "<tr>
                                <td>{$no}</td>
                                <td>{$row['nama_kelas']}</td>
                                <td>{$row['nama_paket']}</td>
                                <td>{$row['nama_mapel']}</td>
                                <td>{$row['tanggal']}</td>
                                <td>{$row['jam_mulai']} - {$row['jam_selesai']}</td>
                                <td>
                                    <button class='btn btn-success btn-sm' onclick=\"showForm({$row['id']}, '{$row['nama_kelas']}', '{$row['nama_paket']}', '{$row['nama_mapel']}', '{$row['tanggal']}')\">Isi Absensi</button>
                                </td>
                              </tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>Tidak ada jadwal ditemukan</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Form Isi Absensi (hidden awalnya) -->
    <div id="formAbsensi" style="display:none;">
        <h4>Isi Absensi</h4>
        <form method="POST">
            <input type="hidden" name="jadwal_id" id="form_jadwal_id">
            <div class="mb-3">
                <label>Kelas</label>
                <input type="text" id="form_kelas" class="form-control" readonly>
            </div>
            <div class="mb-3">
                <label>Paket</label>
                <input type="text" id="form_paket" class="form-control" readonly>
            </div>
            <div class="mb-3">
                <label>Mata Pelajaran</label>
                <input type="text" id="form_mapel" class="form-control" readonly>
            </div>
            <div class="mb-3">
                <label>Tanggal Jadwal</label>
                <input type="text" id="form_tgl_jadwal" class="form-control" readonly>
            </div>
            <div class="mb-3">
                <label>Tanggal Absensi</label>
                <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="mb-3">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <option value="Hadir">Hadir</option>
                    <option value="Tidak Hadir">Tidak Hadir</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Absensi</button>
            <button type="button" class="btn btn-secondary" onclick="hideForm()">Batal</button>
        </form>
    </div>

    <hr>

    <!-- Riwayat Absensi -->
    <h4>Riwayat Absensi</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-secondary">
                <tr>
                    <th>No</th>
                    <th>Kelas</th>
                    <th>Paket</th>
                    <th>Mata Pelajaran</th>
                    <th>Tanggal Absensi</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $riwayat = mysqli_query($conn, "SELECT a.*, k.nama_kelas, p.nama_paket, m.nama_mapel
                                                FROM absensi_tutor a
                                                LEFT JOIN jadwal_offline j ON a.jadwal_id = j.id
                                                LEFT JOIN kelas k ON j.kelas_id = k.id
                                                LEFT JOIN paket p ON j.paket_id = p.id
                                                LEFT JOIN mapel m ON j.mapel_id = m.id
                                                WHERE a.tutor_id = '$tutor_id'
                                                ORDER BY a.tanggal DESC");
                if (mysqli_num_rows($riwayat) > 0) {
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($riwayat)) {
                        echo "<tr>
                                <td>{$no}</td>
                                <td>{$row['nama_kelas']}</td>
                                <td>{$row['nama_paket']}</td>
                                <td>{$row['nama_mapel']}</td>
                                <td>{$row['tanggal']}</td>
                                <td>{$row['status']}</td>
                                <td>{$row['keterangan']}</td>
                              </tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>Belum ada absensi</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function showForm(id, kelas, paket, mapel, tgl) {
    document.getElementById('formAbsensi').style.display = 'block';
    document.getElementById('form_jadwal_id').value = id;
    document.getElementById('form_kelas').value = kelas;
    document.getElementById('form_paket').value = paket;
    document.getElementById('form_mapel').value = mapel;
    document.getElementById('form_tgl_jadwal').value = tgl;
    window.scrollTo(0, document.getElementById('formAbsensi').offsetTop - 20);
}

function hideForm() {
    document.getElementById('formAbsensi').style.display = 'none';
}
</script>

<?php include '../includes/tutor_footer.php'; ?>
