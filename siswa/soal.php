<?php
session_start();
include '../config/database.php';
$title = "Latihan Siswa";
include '../includes/siswa_header_langganan.php';

// Pastikan user adalah siswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../index.php");
    exit;
}

$siswa_id = $_SESSION['user']['id'];
$kelas_id = $_SESSION['user']['kelas_id'] ?? null;

// Ambil kategori dari filter (kalau ada), default = 0 (semua mapel)
$kategori_id = isset($_GET['kategori_id']) ? (int)$_GET['kategori_id'] : 0;


// Ambil semua kategori untuk dropdown
$sqlKategori = "SELECT DISTINCT k.id, k.nama_kategori
                FROM kategori_materi k
                JOIN latihan l ON l.kategori_id = k.id
                WHERE l.kelas_id = ? 
                  AND l.tanggal_publish <= NOW()
                ORDER BY k.nama_kategori";
$stmtKat = $conn->prepare($sqlKategori);
$stmtKat->bind_param("i", $kelas_id);
$stmtKat->execute();
$kategoriRes = $stmtKat->get_result();


// --- PAGINATION ---
$limit = 10; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$sqlCount = "SELECT COUNT(*) as total 
             FROM latihan 
             WHERE kelas_id = ? 
               AND tanggal_publish <= NOW()";
$params = [$kelas_id];
$types = "i";

if ($kategori_id > 0) {
    $sqlCount .= " AND kategori_id = ?";
    $params[] = $kategori_id;
    $types .= "i";
}

$stmtCount = $conn->prepare($sqlCount);
$stmtCount->bind_param($types, ...$params);
$stmtCount->execute();
$totalData = $stmtCount->get_result()->fetch_assoc()['total'] ?? 0;
$totalPages = ceil($totalData / $limit);

// Ambil daftar latihan
$sql = "SELECT l.*, k.nama_kategori 
        FROM latihan l
        JOIN kategori_materi k ON l.kategori_id = k.id
        WHERE l.kelas_id = ? 
          AND l.tanggal_publish <= NOW()";
$params = [$kelas_id];
$types = "i";

if ($kategori_id > 0) {
    $sql .= " AND l.kategori_id = ?";
    $params[] = $kategori_id;
    $types .= "i";
}

$sql .= " ORDER BY l.tanggal_publish DESC
          LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="container mt-4">
    <h3>Daftar Latihan</h3>

    <!-- Filter Mapel -->
    <form method="get" class="mb-3 d-flex gap-2">
        <select name="kategori_id" class="form-select" style="max-width:250px">
            <option value="0">-- Semua Mapel --</option>
            <?php while ($kat = $kategoriRes->fetch_assoc()): ?>
                <option value="<?= $kat['id'] ?>" 
                    <?= $kategori_id == $kat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>


    <div class="list-group">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $now = date('Y-m-d H:i:s');
                $tenggat = $row['tenggat_waktu'];

                // Cek pengerjaan
                $cek = $conn->prepare("SELECT nilai, waktu_selesai 
                                       FROM latihan_siswa 
                                       WHERE siswa_id = ? AND latihan_id = ? 
                                       LIMIT 1");
                $cek->bind_param("ii", $siswa_id, $row['id']);
                $cek->execute();
                $resCek = $cek->get_result();
                $sudah_dikerjakan = $resCek && $resCek->num_rows > 0;
                $jawaban = $sudah_dikerjakan ? $resCek->fetch_assoc() : null;
            ?>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= htmlspecialchars($row['judul']) ?></strong><br>
                            <em><?= htmlspecialchars($row['nama_kategori']) ?></em><br>
                            <?php if ($sudah_dikerjakan): ?>
                                Nilai: <strong>
                                    <?= $jawaban['nilai'] !== null ? $jawaban['nilai'] : 'Belum dinilai' ?>
                                </strong><br>
                                Dikerjakan: 
                                <?= $jawaban['waktu_selesai'] 
                                    ? date('d-m-Y H:i', strtotime($jawaban['waktu_selesai'])) 
                                    : '-' ?>
                            <?php else: ?>
                                Durasi: <?= $row['durasi_menit'] ?> menit | 
                                Deadline: <?= date('d-m-Y H:i', strtotime($tenggat)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if ($sudah_dikerjakan): ?>
                                <span class="badge bg-success">Sudah Dikerjakan</span>
                                <!-- Tombol Detail -->
                                <button 
                                    class="btn btn-info btn-sm detail-btn" 
                                    data-id="<?= $row['id'] ?>" 
                                    data-judul="<?= htmlspecialchars($row['judul']) ?>">
                                    Detail
                                </button>
                            <?php elseif ($now > $tenggat): ?>
                                <span class="badge bg-danger">Lewat Tenggat</span>
                            <?php else: ?>
                                <!-- Tombol Kerjakan -->
                                <button 
                                    class="btn btn-primary btn-sm kerjakan-btn" 
                                    data-id="<?= $row['id'] ?>" 
                                    data-judul="<?= htmlspecialchars($row['judul']) ?>">
                                    Kerjakan
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">Belum ada latihan tersedia.</div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-3">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>">Prev</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="konfirmasiKerjakan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Mulai Latihan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Apakah kamu yakin ingin mulai latihan <span id="judulLatihanPopup"></span>?  
        Waktu akan langsung berjalan setelah latihan dimulai.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <a href="#" id="konfirmasiMulaiBtn" class="btn btn-primary">Mulai Sekarang</a>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailLatihan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Jawaban <span id="judulDetail"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="isiDetail">
        Memuat...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
// Tombol Kerjakan (sudah ada)
document.querySelectorAll('.kerjakan-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('judulLatihanPopup').textContent = this.dataset.judul;
        document.getElementById('konfirmasiMulaiBtn').href = 'kerjakan.php?id=' + this.dataset.id;
        new bootstrap.Modal(document.getElementById('konfirmasiKerjakan')).show();
    });
});

// Tombol Detail
document.querySelectorAll('.detail-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        let latihanId = this.dataset.id;
        let judul = this.dataset.judul;

        document.getElementById('judulDetail').textContent = judul;
        document.getElementById('isiDetail').innerHTML = "Memuat...";

        // Ambil data via AJAX
        fetch("preview_jawaban.php?latihan_id=" + latihanId)
            .then(res => res.text())
            .then(data => {
                document.getElementById('isiDetail').innerHTML = data;
            });

        new bootstrap.Modal(document.getElementById('detailLatihan')).show();
    });
});
</script>

<?php include '../includes/footer.php'; ?>
