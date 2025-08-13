<?php
include '../includes/auth.php';
include '../includes/tutor_header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'tutor') {
    header('Location: ../index.php');
    exit;
}

$tutor_id = $_SESSION['user']['id'];

// Karena tabel latihan_siswa tidak ada tutor_id, maka kita join ke tabel latihan yang punya tutor_id
$where = "WHERE l.tutor_id = '$tutor_id'";

// Filter kelas
if (!empty($_GET['kelas_id'])) {
    $kelas_id = mysqli_real_escape_string($conn, $_GET['kelas_id']);
    $where .= " AND u.kelas_id = '$kelas_id'";
}

// Filter tanggal (tanggal mulai latihan)
if (!empty($_GET['tanggal_awal']) && !empty($_GET['tanggal_akhir'])) {
    $awal = $_GET['tanggal_awal'] . " 00:00:00";
    $akhir = $_GET['tanggal_akhir'] . " 23:59:59";
    $where .= " AND ls.waktu_mulai BETWEEN '$awal' AND '$akhir'";
}

// Ambil data rekap dari latihan_siswa
$query = mysqli_query($conn, "
    SELECT 
        ls.siswa_id AS user_id,
        u.nama AS nama_siswa,
        l.id AS latihan_id,
        l.judul AS judul_latihan,
        ls.nilai,
        ls.waktu_mulai,
        ls.waktu_selesai,
        ls.status
    FROM latihan_siswa ls
    JOIN users u ON ls.siswa_id = u.id
    JOIN latihan l ON ls.latihan_id = l.id
    $where
    ORDER BY l.judul ASC, u.nama ASC
");

?>

<div class="content">
    <div class="card shadow-sm p-4 mb-4">
        <h3 class="fw-bold text-primary mb-4">📄 Rekap Nilai Siswa</h3>

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="kelas" class="form-label">Kelas</label>
                <select name="kelas_id" id="kelas" class="form-select">
                    <option value="">Semua</option>
                    <?php
                    $kelas_query = mysqli_query($conn, "SELECT id, nama_kelas FROM kelas");
                    while ($k = mysqli_fetch_assoc($kelas_query)) {
                        $selected = ($_GET['kelas_id'] ?? '') == $k['id'] ? 'selected' : '';
                        echo "<option value='{$k['id']}' $selected>" . htmlspecialchars($k['nama_kelas'] ?? '', ENT_QUOTES, 'UTF-8') . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Dari Tanggal</label>
                <input type="date" name="tanggal_awal" class="form-control" value="<?= htmlspecialchars($_GET['tanggal_awal'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label>Sampai Tanggal</label>
                <input type="date" name="tanggal_akhir" class="form-control" value="<?= htmlspecialchars($_GET['tanggal_akhir'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="?" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Latihan</th>
                        <th>Nilai</th>
                        <th>Status</th>
                        <th>Waktu Mulai</th>
                        <th>Waktu Selesai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td><?= $no ?></td>
                                <td><?= htmlspecialchars($row['nama_siswa'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['judul_latihan'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($row['nilai'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['status'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['waktu_mulai'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['waktu_selesai'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="toggleDetail('detail-<?= $no ?>')">Lihat Detail</button>
                                </td>
                            </tr>

                            <tr id="detail-<?= $no ?>" style="display:none;">
                                <td colspan="8">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Pertanyaan</th>
                                                <th>Jawaban Siswa</th>
                                                <th>Kunci Jawaban</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $user_id = $row['user_id'];
                                            $latihan_id = $row['latihan_id'];

                                            // Detail jawaban per soal dari jawaban_siswa
                                            $detail = mysqli_query($conn, "
                                                SELECT js.jawaban, js.benar, s.pertanyaan, s.jawaban AS kunci
                                                FROM jawaban_siswa js
                                                JOIN soal s ON js.soal_id = s.id
                                                WHERE js.user_id = '$user_id' AND s.latihan_id = '$latihan_id'
                                            ");

                                            $no_d = 1;
                                            while ($d = mysqli_fetch_assoc($detail)):
                                            ?>
                                            <tr>
                                                <td><?= $no_d ?></td>
                                                <td><?= htmlspecialchars($d['pertanyaan'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($d['jawaban'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($d['kunci'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td>
                                                    <?= $d['benar'] ? '<span class="text-success fw-bold">Benar</span>' : '<span class="text-danger fw-bold">Salah</span>' ?>
                                                </td>
                                            </tr>
                                            <?php 
                                            $no_d++;
                                            endwhile; ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        <?php 
                        $no++;
                        endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8">Belum ada data latihan siswa.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleDetail(id) {
    const el = document.getElementById(id);
    el.style.display = (el.style.display === 'none') ? '' : 'none';
}
</script>

<?php include '../includes/tutor_footer.php'; ?>
