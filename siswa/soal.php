<?php
session_start();
include '../config/database.php';
include '../includes/siswa_header_langganan.php';

// Pastikan user adalah siswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../index.php");
    exit;
}

$siswa_id = $_SESSION['user']['id'];
$kelas_id = $_SESSION['user']['kelas_id'] ?? null;

// Tanggal hari ini
$today = date('Y-m-d');

// Ambil daftar latihan sesuai tanggal publish

$sql = "SELECT * FROM latihan 
        WHERE kelas_id = ? 
        AND tanggal_publish <= NOW()
        AND tenggat_waktu <= NOW()
        ORDER BY tanggal_publish DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $kelas_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="container mt-4">
    <h3>Daftar Latihan</h3>
    <div class="list-group">
        <?php while ($row = $result->fetch_assoc()): 
            $now = date('Y-m-d H:i:s');
            $tenggat = $row['tenggat_waktu'];

            // Cek apakah sudah pernah dikerjakan
            $cek = $conn->prepare("SELECT 1 FROM jawaban_siswa WHERE user_id = ? AND latihan_id = ? LIMIT 1");
            $cek->bind_param("ii", $siswa_id, $row['id']);
            $cek->execute();
            $sudah_dikerjakan = $cek->get_result()->num_rows > 0;
        ?>
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= htmlspecialchars($row['judul']) ?></strong><br>
                        Durasi: <?= $row['durasi_menit'] ?> menit | Deadline: <?= date('d-m-Y H:i', strtotime($tenggat)) ?>
                    </div>
                    <div>
                        <?php if ($sudah_dikerjakan): ?>
                            <span class="badge bg-success">Sudah Dikerjakan</span>
                        <?php elseif ($now > $tenggat): ?>
                            <span class="badge bg-danger">Lewat Tenggat</span>
                        <?php else: ?>
                            <a href="kerjakan.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Kerjakan</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Modal Detail Latihan -->
<div class="modal fade" id="latihanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="judulLatihan"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deskripsiLatihan"></p>
                <p><strong>Durasi:</strong> <span id="durasiLatihan"></span> menit</p>
                <p><strong>Deadline:</strong> <span id="tenggatLatihan"></span></p>
            </div>
            <div class="modal-footer">
                <a id="mulaiBtn" class="btn btn-primary">Mulai Mengerjakan</a>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.latihan-item').forEach(function(item) {
    item.addEventListener('click', function(e) {
        e.preventDefault(); // Cegah link default cuma untuk item ini

        document.getElementById('judulLatihan').textContent = this.dataset.judul;
        document.getElementById('deskripsiLatihan').textContent = this.dataset.deskripsi || 'Tidak ada deskripsi';
        document.getElementById('durasiLatihan').textContent = this.dataset.durasi;
        document.getElementById('tenggatLatihan').textContent = this.dataset.tenggat;
        document.getElementById('mulaiBtn').href = 'kerjakan.php?id=' + this.dataset.id;

        new bootstrap.Modal(document.getElementById('latihanModal')).show();
    });
});
</script>

<style>