<?php
include '../includes/admin_header.php';
include '../config/database.php';

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $pesan = $_POST['pesan'];
    $penerima_id = $_POST['penerima_id'] !== '' ? $_POST['penerima_id'] : NULL;

    $stmt = $conn->prepare("INSERT INTO notifikasi (judul, pesan, penerima_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $judul, $pesan, $penerima_id);
    $stmt->execute();
    $stmt->close();

    echo "<div class='alert alert-success text-center'>Notifikasi berhasil dikirim!</div>";
}

// Ambil data siswa
$siswa_query = "SELECT id, nama FROM users WHERE role = 'siswa'";
$siswa_result = $conn->query($siswa_query);

// Ambil data notifikasi
$notif_query = "SELECT n.*, u.nama AS nama_siswa 
                FROM notifikasi n 
                LEFT JOIN users u ON n.penerima_id = u.id 
                ORDER BY n.tanggal DESC";
$notif_result = $conn->query($notif_query);
?>

<!-- ✅ BUNGKUS semua konten dalam div.content -->
<div class="content">
  <h4 class="mb-4 mt-3">📢 Kelola Notifikasi</h4>

  <!-- Form Kirim Notifikasi -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form method="POST">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="judul" class="form-label">Judul Notifikasi</label>
            <input type="text" class="form-control" id="judul" name="judul" required>
          </div>
          <div class="col-md-6">
            <label for="penerima_id" class="form-label">Kirim ke Siswa Tertentu (opsional)</label>
            <select class="form-select" name="penerima_id" id="penerima_id">
              <option value="">-- Semua Siswa --</option>
              <?php while ($siswa = $siswa_result->fetch_assoc()): ?>
                <option value="<?= $siswa['id'] ?>"><?= htmlspecialchars($siswa['nama']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="mt-3">
          <label for="pesan" class="form-label">Pesan</label>
          <textarea class="form-control" id="pesan" name="pesan" rows="3" required></textarea>
        </div>
        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Kirim Notifikasi</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Daftar Notifikasi -->
  <div class="card shadow-sm mb-5">
    <div class="card-body">
      <h5 class="card-title mb-3">📄 Daftar Notifikasi Terkirim</h5>
      <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
        <table class="table table-bordered table-hover align-middle text-center">
          <thead class="table-dark">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Judul</th>
              <th scope="col">Pesan</th>
              <th scope="col">Penerima</th>
              <th scope="col">Tanggal</th>
              <th scope="col">Status</th>
            </tr>
          </thead>
          <tbody class="text-start">
            <?php if ($notif_result->num_rows > 0): ?>
              <?php $no = 1; while ($notif = $notif_result->fetch_assoc()): ?>
                <tr>
                  <td class="text-center"><?= $no++ ?></td>
                  <td class="fw-semibold"><?= htmlspecialchars($notif['judul']) ?></td>
                  <td style="white-space: pre-wrap;"><?= htmlspecialchars($notif['pesan']) ?></td>
                  <td><?= $notif['nama_siswa'] ?? '<i class="text-muted">Semua Siswa</i>' ?></td>
                  <td class="small text-muted"><?= date('d M Y - H:i', strtotime($notif['tanggal'])) ?></td>
                  <td>
                    <span class="badge bg-<?= $notif['status_baca'] === 'sudah' ? 'success' : 'secondary' ?>">
                      <?= ucfirst($notif['status_baca']) ?>
                    </span>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center text-muted">Belum ada notifikasi dikirim.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
