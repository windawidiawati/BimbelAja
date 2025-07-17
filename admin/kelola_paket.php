<?php
include '../includes/auth.php';
include '../includes/admin_header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'admin') {
  header('Location: ../index.php'); exit;
}

// Tambah / Edit
$edit_mode = isset($_GET['edit']);
$edit_data = null;
if ($edit_mode) {
  $id = $_GET['edit'];
  $query = mysqli_query($conn, "SELECT * FROM paket WHERE id = $id");
  $edit_data = mysqli_fetch_assoc($query);
}

// Simpan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? '';
  $nama = $_POST['nama'];
  $kategori = $_POST['kategori'];
  $jenjang = $_POST['jenjang'];
  $kelas = $_POST['kelas'];
  $harga = (int)$_POST['harga'];
  $durasi = (int)$_POST['durasi'];
  $satuan_durasi = $_POST['satuan_durasi'];
  $deskripsi = $_POST['deskripsi'];
  $status = $_POST['status'];

  if ($id) {
    $stmt = $conn->prepare("UPDATE paket SET nama=?, kategori=?, jenjang=?, kelas=?, harga=?, durasi=?, satuan_durasi=?, deskripsi=?, status=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("ssssissssi", $nama, $kategori, $jenjang, $kelas, $harga, $durasi, $satuan_durasi, $deskripsi, $status, $id);
    $stmt->execute();
  } else {
    $stmt = $conn->prepare("INSERT INTO paket (nama, kategori, jenjang, kelas, harga, durasi, satuan_durasi, deskripsi, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("ssssissss", $nama, $kategori, $jenjang, $kelas, $harga, $durasi, $satuan_durasi, $deskripsi, $status);
    $stmt->execute();
  }
  header("Location: kelola_paket.php");
  exit;
}

// Hapus
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  mysqli_query($conn, "DELETE FROM paket WHERE id = $id");
  header("Location: kelola_paket.php");
  exit;
}

// Data
$paket = mysqli_query($conn, "SELECT * FROM paket ORDER BY harga ASC");
?>

<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Kelola Paket Langganan</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paketModal">+ Tambah Paket</button>
  </div>

  <!-- Tabel -->
  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th>Nama</th>
          <th>Kategori</th>
          <th>Jenjang</th>
          <th>Kelas</th>
          <th>Harga</th>
          <th>Durasi</th>
          <th>Deskripsi</th>
          <th>Status</th>
          <th width="160px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = mysqli_fetch_assoc($paket)): ?>
        <tr>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= $row['kategori'] ?></td>
          <td><?= $row['jenjang'] ?></td>
          <td><?= $row['kelas'] ?></td>
          <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
          <td><?= $row['durasi'].' '.$row['satuan_durasi'] ?></td>
          <td><?= htmlspecialchars($row['deskripsi']) ?></td>
          <td><?= $row['status'] ?></td>
          <td>
            <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus paket ini?')">Hapus</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="paketModal" tabindex="-1" aria-labelledby="paketModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><?= $edit_mode ? 'Edit Paket' : 'Tambah Paket' ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label>Nama Paket</label>
            <input type="text" name="nama" class="form-control" required value="<?= $edit_data['nama'] ?? '' ?>">
          </div>
          <div class="mb-2">
            <label>Kategori</label>
            <select name="kategori" class="form-select" required>
              <option value="">-- Pilih --</option>
              <?php foreach (['Basic', 'Premium'] as $k): ?>
              <option value="<?= $k ?>" <?= ($edit_data['kategori'] ?? '') == $k ? 'selected' : '' ?>><?= $k ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label>Jenjang</label>
            <select name="jenjang" id="jenjang" class="form-select" onchange="updateKelasOptions()" required>
              <option value="">-- Pilih --</option>
              <?php foreach (['SD','SMP','SMA'] as $j): ?>
              <option value="<?= $j ?>" <?= ($edit_data['jenjang'] ?? '') == $j ? 'selected' : '' ?>><?= $j ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label>Kelas</label>
            <select name="kelas" id="kelas" class="form-select" required>
              <?php if (!empty($edit_data['kelas'])): ?>
              <option value="<?= $edit_data['kelas'] ?>" selected>Kelas <?= $edit_data['kelas'] ?></option>
              <?php else: ?>
              <option value="">-- Pilih --</option>
              <?php endif; ?>
            </select>
          </div>
          <div class="mb-2">
            <label>Harga (Rp)</label>
            <input type="number" name="harga" class="form-control" required value="<?= $edit_data['harga'] ?? '' ?>">
          </div>
          <div class="mb-2">
            <label>Durasi</label>
            <div class="input-group">
              <input type="number" name="durasi" class="form-control" required value="<?= $edit_data['durasi'] ?? '' ?>">
              <select name="satuan_durasi" class="form-select" required>
                <option value="bulan" <?= ($edit_data['satuan_durasi'] ?? '') === 'bulan' ? 'selected' : '' ?>>Bulan</option>
                <option value="tahun" <?= ($edit_data['satuan_durasi'] ?? '') === 'tahun' ? 'selected' : '' ?>>Tahun</option>
              </select>
            </div>
          </div>
          <div class="mb-2">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control" required><?= $edit_data['deskripsi'] ?? '' ?></textarea>
          </div>
          <div class="mb-2">
            <label>Status</label>
            <select name="status" class="form-select" required>
              <option value="aktif" <?= ($edit_data['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
              <option value="nonaktif" <?= ($edit_data['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><?= $edit_mode ? 'Update' : 'Tambah' ?></button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function updateKelasOptions() {
  const jenjang = document.getElementById('jenjang').value;
  const kelasSelect = document.getElementById('kelas');
  let options = [];

  if (jenjang === 'SD') options = ['1','2','3','4','5','6'];
  if (jenjang === 'SMP') options = ['7','8','9'];
  if (jenjang === 'SMA') options = ['10','11','12'];

  kelasSelect.innerHTML = '<option value="">-- Pilih --</option>';
  options.forEach(k => {
    const opt = document.createElement('option');
    opt.value = k;
    opt.textContent = 'Kelas ' + k;
    kelasSelect.appendChild(opt);
  });
}
</script>

<?php include '../includes/admin_footer.php'; ?>
