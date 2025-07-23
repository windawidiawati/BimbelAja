<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'tutor') {
    header('Location: ../index.php');
    exit;
}
include '../includes/tutor_header.php';
include '../config/database.php';

$tutor_id = $_SESSION['user']['id'];

// Validasi ID latihan
if (!isset($_GET['id'])) {
    header('Location: buat_soal.php');
    exit;
}

$latihan_id = (int) $_GET['id'];

// --- Proses hapus soal ---
if (isset($_GET['hapus_soal'])) {
    $soal_id = (int) $_GET['hapus_soal'];
    mysqli_query($conn, "DELETE FROM soal WHERE id = $soal_id AND latihan_id = $latihan_id AND tutor_id = $tutor_id");
    header("Location: lihat_soal.php?id=$latihan_id");
    exit;
}

// Ambil info latihan
$query = "SELECT l.*, k.nama_kelas, km.nama_kategori
          FROM latihan l
          LEFT JOIN kelas k ON l.kelas_id = k.id
          LEFT JOIN kategori_materi km ON l.kategori_id = km.id
          WHERE l.id = $latihan_id AND l.tutor_id = $tutor_id";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<div class='alert alert-danger'>Latihan tidak ditemukan.</div>";
    include '../includes/tutor_footer.php';
    exit;
}

// Filter pencarian
$filter = '';
$keyword = '';
if (!empty($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($conn, $_GET['cari']);
    $filter = "AND s.pertanyaan LIKE '%$keyword%'";
}

// Ambil daftar soal
$query_soal = "SELECT * FROM soal s WHERE s.latihan_id = $latihan_id $filter ORDER BY s.id ASC";
$soal = mysqli_query($conn, $query_soal);

// Hitung total soal
$total_soal = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM soal WHERE latihan_id = $latihan_id"));
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
                <p><b>Total Soal:</b> <?= $total_soal ?></p>
            </div>

            <!-- Form Pencarian -->
            <form method="get" class="mb-3">
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
                                    <a href="lihat_soal.php?id=<?= $latihan_id ?>&hapus_soal=<?= $row['id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Hapus soal ini?')">🗑 Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (mysqli_num_rows($soal) === 0): ?>
                            <tr><td colspan="8" class="text-center">Tidak ada soal.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/tutor_footer.php'; ?>
