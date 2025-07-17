<?php
include '../config/database.php';
include '../includes/admin_header.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
?>

<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Kelola Materi</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah Materi</button>
  </div>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
  <?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-bordered bg-white shadow-sm">
      <thead class="table-primary">
        <tr>
          <th>Judul</th>
          <th>Deskripsi</th>
          <th>Kategori</th>
          <th>Kelas</th>
          <th>File</th>
          <th>Tipe</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $query = "
          SELECT m.*, k.nama_kategori, kl.nama_kelas
          FROM materi m
          LEFT JOIN kategori_materi k ON m.kategori_id = k.id
          LEFT JOIN kelas kl ON m.kelas_id = kl.id
          ORDER BY m.created_at DESC";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0):
          while ($row = mysqli_fetch_assoc($result)):
        ?>
        <tr>
          <td><?= htmlspecialchars($row['judul']) ?></td>
          <td><?= substr(htmlspecialchars($row['deskripsi']), 0, 50) ?>...</td>
          <td><?= $row['nama_kategori'] ?: '-' ?></td>
          <td><?= $row['nama_kelas'] ?: '-' ?></td>
          <td>
            <a href="../assets/uploads/<?= $row['file'] ?>" target="_blank" class="text-primary">
              <?= $row['tipe_file'] == 'video' ? 'Tonton Video' : 'Lihat PDF' ?>
            </a>
          </td>
          <td><?= ucfirst($row['tipe_file']) ?></td>
          <td>
            <span class="badge 
              <?= $row['status'] == 'diterima' ? 'bg-success' : ($row['status'] == 'ditolak' ? 'bg-danger' : 'bg-warning text-dark') ?>">
              <?= ucfirst($row['status']) ?>
            </span>
          </td>
          <td>
            <form action="proses_materi.php" method="POST" class="d-inline">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button name="setujui" class="btn btn-success btn-sm" onclick="return confirm('Setujui materi ini?')">✔</button>
            </form>
            <form action="proses_materi.php" method="POST" class="d-inline">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button name="tolak" class="btn btn-danger btn-sm" onclick="return confirm('Tolak materi ini?')">✖</button>
            </form>
            <form action="proses_materi.php" method="POST" class="d-inline">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button name="hapus" class="btn btn-secondary btn-sm" onclick="return confirm('Hapus materi ini?')">🗑</button>
            </form>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="8" class="text-center">Belum ada materi.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah Materi -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="proses_materi.php" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahLabel">Tambah Materi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Judul Materi</label>
          <input type="text" name="judul" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Deskripsi</label>
          <textarea name="deskripsi" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
          <label>Kategori</label>
          <select name="kategori_id" class="form-select" required>
            <option value="">-- Pilih Kategori --</option>
            <?php
            $kategori = mysqli_query($conn, "SELECT * FROM kategori_materi");
            while ($k = mysqli_fetch_assoc($kategori)):
            ?>
              <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="mb-3">
          <label>Kelas</label>
          <select name="kelas_id" class="form-select" required>
            <option value="">-- Pilih Kelas --</option>
            <?php
            $kelas = mysqli_query($conn, "SELECT * FROM kelas");
            while ($k = mysqli_fetch_assoc($kelas)):
            ?>
              <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="mb-3">
          <label>File Materi (PDF/Video)</label>
          <input type="file" name="file" class="form-control" accept=".pdf,.mp4,.mkv,.avi,.mov" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="tambah_admin" class="btn btn-primary">Unggah Materi</button>
      </div>
    </form>
  </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
