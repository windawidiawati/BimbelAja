<?php
include '../includes/auth.php';
include '../includes/tutor_header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'tutor') {
    header('Location: ../index.php');
    exit;
}

$tutor_id = $_SESSION['user']['id'];

// WHERE Clause
$where = "WHERE l.tutor_id = '$tutor_id'";

// Tambahkan filter jika ada
if (!empty($_GET['kelas_id'])) {
    $kelas_id = mysqli_real_escape_string($conn, $_GET['kelas_id']);
    $where .= " AND u.kelas_id = '$kelas_id'";
}
if (!empty($_GET['latihan_id'])) {
    $latihan_id = mysqli_real_escape_string($conn, $_GET['latihan_id']);
    $where .= " AND l.id = '$latihan_id'";
}
if (isset($_GET['status']) && ($_GET['status'] === '0' || $_GET['status'] === '1')) {
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    $where .= " AND js.benar = '$status'";
}
if (!empty($_GET['tanggal_awal']) && !empty($_GET['tanggal_akhir'])) {
    $awal = $_GET['tanggal_awal'] . " 00:00:00";
    $akhir = $_GET['tanggal_akhir'] . " 23:59:59";
    $where .= " AND js.tanggal BETWEEN '$awal' AND '$akhir'";
}

// Query
$query = mysqli_query($conn, "
    SELECT 
        js.id,
        u.nama AS nama_siswa,
        s.pertanyaan,
        js.jawaban,
        js.benar,
        js.tanggal,
        l.judul AS judul_latihan
    FROM jawaban_siswa js
    JOIN users u ON js.user_id = u.id
    JOIN soal s ON js.soal_id = s.id
    JOIN latihan l ON s.latihan_id = l.id
    $where
    ORDER BY js.tanggal DESC
");
?>

<div class="content">
    <div class="card shadow-sm p-4 mb-4">
        <h3 class="fw-bold text-primary mb-4">📄 Nilai Siswa</h3>

        <!-- Filter Form -->
        <form method="GET" class="row g-3 mb-4">
            <!-- Kelas -->
            <div class="col-md-3">
                <label for="kelas" class="form-label">Kelas</label>
                <select name="kelas_id" id="kelas" class="form-select">
                    <option value="">Semua</option>
                    <?php
                    $kelas_query = mysqli_query($conn, "SELECT id, nama_kelas FROM kelas");
                    while ($k = mysqli_fetch_assoc($kelas_query)) {
                        $selected = ($_GET['kelas_id'] ?? '') == $k['id'] ? 'selected' : '';
                        echo "<option value='{$k['id']}' $selected>{$k['nama_kelas']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Latihan -->
            <div class="col-md-3">
                <label for="latihan" class="form-label">Latihan</label>
                <select name="latihan_id" id="latihan" class="form-select">
                    <option value="">Semua</option>
                    <?php
                    $latihan_query = mysqli_query($conn, "SELECT id, judul FROM latihan WHERE tutor_id = '$tutor_id'");
                    while ($l = mysqli_fetch_assoc($latihan_query)) {
                        $selected = ($_GET['latihan_id'] ?? '') == $l['id'] ? 'selected' : '';
                        echo "<option value='{$l['id']}' $selected>{$l['judul']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Status -->
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="1" <?= ($_GET['status'] ?? '') === '1' ? 'selected' : '' ?>>Benar</option>
                    <option value="0" <?= ($_GET['status'] ?? '') === '0' ? 'selected' : '' ?>>Salah</option>
                </select>
            </div>

            <!-- Tanggal -->
            <div class="col-md-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="tanggal_awal" class="form-control" value="<?= $_GET['tanggal_awal'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="tanggal_akhir" class="form-control" value="<?= $_GET['tanggal_akhir'] ?? '' ?>">
            </div>

            <!-- Tombol -->
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-filter-circle"></i> Filter</button>
                <a href="?" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
            </div>
        </form>

        <!-- Tabel -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Latihan</th>
                        <th>Pertanyaan</th>
                        <th>Jawaban</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                                <td><?= htmlspecialchars($row['judul_latihan']) ?></td>
                                <td><?= htmlspecialchars($row['pertanyaan']) ?></td>
                                <td><?= htmlspecialchars($row['jawaban']) ?></td>
                                <td>
                                    <?php if ($row['benar'] == 1): ?>
                                        <span class="badge bg-success">Benar</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Salah</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Belum ada jawaban siswa.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/tutor_footer.php'; ?>
