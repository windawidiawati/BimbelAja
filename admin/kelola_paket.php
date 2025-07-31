<?php
ob_start();
include '../includes/auth.php';
include '../includes/admin_header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
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
    <button class="btn btn-primary" onclick="openTambahModal()">+ Tambah Paket</button>
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
            <button class="btn btn-sm btn-warning" onclick='openEditModal(<?= json_encode($row) ?>)'>Edit</button>
            <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus paket ini?')">Hapus</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="paketModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="id" id="paket-id">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modal-title">Tambah Paket</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2"><label>Nama Paket</label><input type="text" name="nama" id="paket-nama" class="form-control" required></div>
          <div class="mb-2">
            <label>Kategori</label>
            <select name="kategori" id="paket-kategori" class="form-select" required>
              <option value="">-- Pilih --</option>
              <option value="Basic">Basic</option>
              <option value="Premium">Premium</option>
            </select>
          </div>
          <div class="mb-2">
            <label>Jenjang</label>
            <select name="jenjang" id="paket-jenjang" class="form-select" onchange="updateKelasOptions()" required>
              <option value="">-- Pilih --</option>
              <option value="SD">SD</option>
              <option value="SMP">SMP</option>
              <option value="SMA">SMA</option>
            </select>
          </div>
          <div class="mb-2">
            <label>Kelas</label>
            <select name="kelas" id="paket-kelas" class="form-select" required></select>
          </div>
          <div class="mb-2"><label>Harga (Rp)</label><input type="number" name="harga" id="paket-harga" class="form-control" required></div>
          <div class="mb-2">
            <label>Durasi</label>
            <div class="input-group">
              <input type="number" name="durasi" id="paket-durasi" class="form-control" required>
              <select name="satuan_durasi" id="paket-satuan" class="form-select" required>
                <option value="bulan">Bulan</option>
                <option value="tahun">Tahun</option>
              </select>
            </div>
          </div>
          <div class="mb-2"><label>Deskripsi</label><textarea name="deskripsi" id="paket-deskripsi" class="form-control" required></textarea></div>
          <div class="mb-2">
            <label>Status</label>
            <select name="status" id="paket-status" class="form-select" required>
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function updateKelasOptions() {
  const jenjang = document.getElementById('paket-jenjang').value;
  const kelasSelect = document.getElementById('paket-kelas');
  let options = [];

  if (jenjang === 'SD') options = ['1','2','3','4','5','6'];
  else if (jenjang === 'SMP') options = ['7','8','9'];
  else if (jenjang === 'SMA') options = ['10','11','12'];

  kelasSelect.innerHTML = '<option value="">-- Pilih --</option>';
  options.forEach(k => {
    const opt = document.createElement('option');
    opt.value = k;
    opt.textContent = 'Kelas ' + k;
    kelasSelect.appendChild(opt);
  });
}

function openTambahModal() {
  document.getElementById('modal-title').innerText = 'Tambah Paket';
  document.querySelector('form').reset();
  document.getElementById('paket-id').value = '';
  new bootstrap.Modal(document.getElementById('paketModal')).show();
}

function openEditModal(data) {
  document.getElementById('modal-title').innerText = 'Edit Paket';
  document.getElementById('paket-id').value = data.id;
  document.getElementById('paket-nama').value = data.nama;
  document.getElementById('paket-kategori').value = data.kategori;
  document.getElementById('paket-jenjang').value = data.jenjang;
  updateKelasOptions();
  setTimeout(() => {
    document.getElementById('paket-kelas').value = data.kelas;
  }, 200);
  document.getElementById('paket-harga').value = data.harga;
  document.getElementById('paket-durasi').value = data.durasi;
  document.getElementById('paket-satuan').value = data.satuan_durasi;
  document.getElementById('paket-deskripsi').value = data.deskripsi;
  document.getElementById('paket-status').value = data.status;
  new bootstrap.Modal(document.getElementById('paketModal')).show();
}
</script>

<?php include '../includes/admin_footer.php'; ?>
<?php ob_end_flush(); ?>
