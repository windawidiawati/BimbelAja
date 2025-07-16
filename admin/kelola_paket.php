<?php
include '../includes/auth.php';
include '../includes/header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'admin') {
  header('Location: ../index.php'); exit;
}

// Tambah / Edit Data
$edit_mode = false;
$edit_data = null;

if (isset($_GET['edit'])) {
  $edit_mode = true;
  $id = $_GET['edit'];
  $query = mysqli_query($conn, "SELECT * FROM paket WHERE id = $id");
  $edit_data = mysqli_fetch_assoc($query);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = $_POST['nama'];
  $kategori = $_POST['kategori'];
  $jenjang = $_POST['jenjang'];
  $kelas = $_POST['kelas'];
  $harga = (int)$_POST['harga'];
  $durasi = (int)$_POST['durasi'];
  $satuan_durasi = $_POST['satuan_durasi'];
  $warna = $_POST['warna'];
  $deskripsi = $_POST['deskripsi'];
  $status = $_POST['status'];

  if (isset($_POST['id']) && $_POST['id'] != '') {
    // Update
    $id = $_POST['id'];
    $stmt = $conn->prepare("UPDATE paket SET nama=?, kategori=?, jenjang=?, kelas=?, harga=?, durasi=?, satuan_durasi=?, warna=?, deskripsi=?, status=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("ssssisssssi", $nama, $kategori, $jenjang, $kelas, $harga, $durasi, $satuan_durasi, $warna, $deskripsi, $status, $id);
    $stmt->execute();
  } else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO paket (nama, kategori, jenjang, kelas, harga, durasi, satuan_durasi, warna, deskripsi, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("ssssisssss", $nama, $kategori, $jenjang, $kelas, $harga, $durasi, $satuan_durasi, $warna, $deskripsi, $status);
    $stmt->execute();
  }

  header("Location: kelola_paket.php");
  exit;
}

// Hapus data
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  mysqli_query($conn, "DELETE FROM paket WHERE id = $id");
  header("Location: kelola_paket.php");
  exit;
}

// Ambil semua data
$paket = mysqli_query($conn, "SELECT * FROM paket ORDER BY harga ASC");
?>

<div class="container mt-5">
  <h3 class="mb-4">Kelola Paket Langganan</h3>

  <!-- Form Tambah / Edit -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-<?= $edit_mode ? 'warning' : 'primary' ?> text-white">
      <?= $edit_mode ? 'Edit Paket' : 'Tambah Paket' ?>
    </div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">

        <div class="mb-3">
          <label>Nama Paket</label>
          <input type="text" name="nama" class="form-control" required value="<?= $edit_data['nama'] ?? '' ?>">
        </div>

        <div class="mb-3">
          <label>Kategori</label>
          <select name="kategori" class="form-select" required>
            <option value="">-- Pilih Kategori --</option>
            <?php
              $kategori_opsi = ['Basic', 'Premium'];
              foreach ($kategori_opsi as $k) {
                $selected = ($edit_data['kategori'] ?? '') === $k ? 'selected' : '';
                echo "<option value='$k' $selected>$k</option>";
              }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label>Jenjang</label>
          <select name="jenjang" id="jenjang" class="form-select" required onchange="updateKelasOptions()">
            <option value="">-- Pilih Jenjang --</option>
            <?php
              $jenjang_opsi = ['SD', 'SMP', 'SMA'];
              foreach ($jenjang_opsi as $jenjang_item) {
                $selected = ($edit_data['jenjang'] ?? '') === $jenjang_item ? 'selected' : '';
                echo "<option value='$jenjang_item' $selected>$jenjang_item</option>";
              }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label>Kelas</label>
          <select name="kelas" id="kelas" class="form-select" required>
            <?php if (!empty($edit_data['kelas'])): ?>
              <option selected value="<?= $edit_data['kelas'] ?>">Kelas <?= $edit_data['kelas'] ?></option>
            <?php else: ?>
              <option value="">-- Pilih Kelas --</option>
            <?php endif; ?>
          </select>
        </div>

        <div class="mb-3">
          <label>Harga (Rp)</label>
          <input type="number" name="harga" class="form-control" required value="<?= $edit_data['harga'] ?? '' ?>">
        </div>

        <div class="mb-3">
          <label>Durasi</label>
          <div class="input-group">
            <input type="number" name="durasi" class="form-control" required value="<?= $edit_data['durasi'] ?? '' ?>">
            <select name="satuan_durasi" class="form-select" required>
              <option value="bulan" <?= ($edit_data['satuan_durasi'] ?? '') === 'bulan' ? 'selected' : '' ?>>Bulan</option>
              <option value="tahun" <?= ($edit_data['satuan_durasi'] ?? '') === 'tahun' ? 'selected' : '' ?>>Tahun</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label>Warna Bootstrap</label>
          <select name="warna" class="form-select" required>
            <?php
              $opsi_warna = ['primary', 'success', 'warning', 'danger', 'info', 'secondary'];
              foreach ($opsi_warna as $w) {
                $selected = ($edit_data['warna'] ?? '') === $w ? 'selected' : '';
                echo "<option value='$w' $selected>$w</option>";
              }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label>Deskripsi</label>
          <textarea name="deskripsi" class="form-control" required><?= $edit_data['deskripsi'] ?? '' ?></textarea>
        </div>

        <div class="mb-3">
          <label>Status</label>
          <select name="status" class="form-select" required>
            <option value="aktif" <?= ($edit_data['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="nonaktif" <?= ($edit_data['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
          </select>
        </div>

        <button type="submit" class="btn btn-<?= $edit_mode ? 'warning' : 'primary' ?>">
          <?= $edit_mode ? 'Update Paket' : 'Tambah Paket' ?>
        </button>
        <?php if ($edit_mode): ?>
          <a href="kelola_paket.php" class="btn btn-secondary">Batal</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <!-- Tabel Paket -->
  <table class="table table-bordered">
    <thead class="table-light">
      <tr>
        <th>Nama Paket</th>
        <th>Kategori</th>
        <th>Jenjang</th>
        <th>Kelas</th>
        <th>Harga</th>
        <th>Durasi</th>
        <th>Deskripsi</th>
        <th>Warna</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = mysqli_fetch_assoc($paket)): ?>
        <tr>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= htmlspecialchars($row['kategori']) ?></td>
          <td><?= htmlspecialchars($row['jenjang']) ?></td>
          <td><?= htmlspecialchars($row['kelas']) ?></td>
          <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
          <td><?= $row['durasi'] . ' ' . htmlspecialchars($row['satuan_durasi'] ?? 'bulan') ?></td>
          <td><?= htmlspecialchars($row['deskripsi']) ?></td>
          <td><span class="badge bg-<?= $row['warna'] ?>"><?= $row['warna'] ?></span></td>
          <td><?= htmlspecialchars($row['status']) ?></td>
          <td>
            <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus paket ini?')">Hapus</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
function updateKelasOptions() {
  const jenjang = document.getElementById('jenjang').value;
  const kelasSelect = document.getElementById('kelas');

  let options = [];
  if (jenjang === 'SD') {
    options = ['1','2','3','4','5','6'];
  } else if (jenjang === 'SMP') {
    options = ['7','8','9'];
  } else if (jenjang === 'SMA') {
    options = ['10','11','12'];
  }

  kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
  options.forEach(k => {
    const opt = document.createElement('option');
    opt.value = k;
    opt.textContent = 'Kelas ' + k;
    kelasSelect.appendChild(opt);
  });
}
</script>

<?php include '../includes/footer.php'; ?>
