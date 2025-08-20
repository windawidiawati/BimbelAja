<?php
ob_start();
include '../includes/auth.php';
include '../includes/admin_header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}

// Handle form submission

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
    // Update paket
    $stmt = $conn->prepare("UPDATE paket SET nama=?, kategori=?, jenjang=?, kelas=?, harga=?, durasi=?, satuan_durasi=?, deskripsi=?, status=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("ssssissssi", $nama, $kategori, $jenjang, $kelas, $harga, $durasi, $satuan_durasi, $deskripsi, $status, $id);
  } else {
    // Ambil ID terakhir +1 supaya tidak 0
    $res = mysqli_query($conn, "SELECT IFNULL(MAX(id), 0) + 1 AS new_id FROM paket");
    $new_id = mysqli_fetch_assoc($res)['new_id'];

    // Insert paket baru
    $stmt = $conn->prepare("INSERT INTO paket (id, nama, kategori, jenjang, kelas, harga, durasi, satuan_durasi, deskripsi, status, created_at, updated_at) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("issssissss", $new_id, $nama, $kategori, $jenjang, $kelas, $harga, $durasi, $satuan_durasi, $deskripsi, $status);
  }

  if ($stmt->execute()) {
    header("Location: kelola_paket.php");
    exit;
  } else {
    $error = "Gagal menyimpan data paket. Error: " . $stmt->error;
  }
}

// Handle delete
if (isset($_GET['hapus'])) {
  $id = (int)$_GET['hapus'];
  mysqli_query($conn, "DELETE FROM paket WHERE id = $id");
  header("Location: kelola_paket.php");
  exit;
}

// Get package data for editing
$edit_data = null;
if (isset($_GET['edit'])) {
  $id = (int)$_GET['edit'];
  $query = mysqli_query($conn, "SELECT * FROM paket WHERE id = $id");
  $edit_data = mysqli_fetch_assoc($query);
}

// Get all packages
$paket = mysqli_query($conn, "SELECT * FROM paket ORDER BY harga ASC");
?>

<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Kelola Paket Langganan</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paketModal" onclick="resetForm()">
      + Tambah Paket
    </button>
  </div>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

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
          <th>Status</th>
          <th width="180px">Aksi</th>
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
          <td>
            <span class="badge bg-<?= $row['status'] === 'aktif' ? 'success' : 'danger' ?>">
              <?= ucfirst($row['status']) ?>
            </span>
          </td>
          <td>
            <button class="btn btn-sm btn-warning"
                    data-bs-toggle="modal"
                    data-bs-target="#paketModal"
                    onclick="editPackage(<?= htmlspecialchars(json_encode($row)) ?>)">
              Edit
            </button>
            <a href="detail_paket.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Detail</a>
            <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
               onclick="return confirm('Hapus paket ini?')">Hapus</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="paketModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="id" id="packageId">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalTitle">Tambah Paket Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama Paket</label>
            <input type="text" name="nama" class="form-control" required>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Kategori</label>
              <select name="kategori" class="form-select" required>
                <option value="">-- Pilih Kategori --</option>
                <option value="Basic">Basic</option>
                <option value="Premium">Premium</option>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Jenjang</label>
              <select name="jenjang" id="jenjang" class="form-select" required onchange="updateKelasOptions()">
                <option value="">-- Pilih Jenjang --</option>
                <option value="SD">SD</option>
                <option value="SMP">SMP</option>
                <option value="SMA">SMA</option>
              </select>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Kelas</label>
              <select name="kelas" id="kelas" class="form-select" required>
                <option value="">-- Pilih Kelas --</option>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Harga (Rp)</label>
              <input type="number" name="harga" class="form-control" required>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Durasi</label>
              <div class="input-group">
                <input type="number" name="durasi" class="form-control" required>
                <select name="satuan_durasi" class="form-select" required>
                  <option value="bulan">Bulan</option>
                  <option value="tahun">Tahun</option>
                </select>
              </div>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select" required>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
              </select>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Update class options based on selected education level
function updateKelasOptions() {
  const jenjang = document.getElementById('jenjang').value;
  const kelasSelect = document.getElementById('kelas');
  
  kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
  
  if (!jenjang) return;
  
  let classes = [];
  if (jenjang === 'SD') classes = ['1', '2', '3', '4', '5', '6'];
  else if (jenjang === 'SMP') classes = ['7', '8', '9'];
  else if (jenjang === 'SMA') classes = ['10', '11', '12'];
  
  classes.forEach(grade => {
    const option = document.createElement('option');
    option.value = grade;
    option.textContent = 'Kelas ' + grade;
    kelasSelect.appendChild(option);
  });
}

// Edit package - fill form with existing data
function editPackage(pkg) {
  document.getElementById('modalTitle').textContent = 'Edit Paket';
  document.getElementById('packageId').value = pkg.id;
  
  const form = document.forms[0];
  form.nama.value = pkg.nama;
  form.kategori.value = pkg.kategori;
  form.jenjang.value = pkg.jenjang;
  
  // Update class options first
  updateKelasOptions();
  // Then set the selected class
  setTimeout(() => {
    form.kelas.value = pkg.kelas;
  }, 100);
  
  form.harga.value = pkg.harga;
  form.durasi.value = pkg.durasi;
  form.satuan_durasi.value = pkg.satuan_durasi;
  form.status.value = pkg.status;
  form.deskripsi.value = pkg.deskripsi;
}

// Reset form for new package
function resetForm() {
  document.getElementById('modalTitle').textContent = 'Tambah Paket Baru';
  document.getElementById('packageId').value = '';
  document.forms[0].reset();
  document.getElementById('kelas').innerHTML = '<option value="">-- Pilih Kelas --</option>';
}
</script>

<?php include '../includes/admin_footer.php'; 
ob_end_flush();
?>