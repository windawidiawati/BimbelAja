<?php
include '../includes/auth.php';
include '../includes/header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}

$user_id = $_SESSION['user']['id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $paket = $_POST['nama'];
  $jenjang = $_POST['jenjang'];
  $kelas = $_POST['kelas'];
  $durasi = (int)$_POST['durasi'];
  $satuan_durasi = $_POST['satuan_durasi'];
  $status = $_POST['status'];

  $tanggal_mulai = date('Y-m-d');
  if ($satuan_durasi === 'bulan') {
    $tanggal_berakhir = date('Y-m-d', strtotime("+$durasi months"));
  } else {
    $tanggal_berakhir = date('Y-m-d', strtotime("+$durasi years"));
  }

  $stmt = $conn->prepare("INSERT INTO langganan (user_id, paket, jenjang, kelas, tanggal_mulai, tanggal_berakhir, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
  if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
  }

  $stmt->bind_param("issssss", $user_id, $paket, $jenjang, $kelas, $tanggal_mulai, $tanggal_berakhir, $status);
  $stmt->execute();

  header("Location: kelola_paket.php");
  exit;
}

  header('Location: ../index.php'); exit;


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
  $deskripsi = $_POST['deskripsi'];
  $status = $_POST['status'];

  if (isset($_POST['id']) && $_POST['id'] != '') {
    // Update
    $id = $_POST['id'];
    $stmt = $conn->prepare("UPDATE paket SET nama=?, kategori=?, jenjang=?, kelas=?, harga=?, durasi=?, satuan_durasi=?, deskripsi=?, status=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("ssssissssi", $nama, $kategori, $jenjang, $kelas, $harga, $durasi, $satuan_durasi, $deskripsi, $status, $id);
    $stmt->execute();
  } else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO paket (nama, kategori, jenjang, kelas, harga, durasi, satuan_durasi, deskripsi, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("ssssissss", $nama, $kategori, $jenjang, $kelas, $harga, $durasi, $satuan_durasi, $deskripsi, $status);
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

<<<<<<< HEAD
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">Tambah Paket Baru</div>
    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
          <label>Nama Paket</label>
          <input type="text" name="nama" class="form-control" required>
=======
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
>>>>>>> origin
        </div>

        <div class="mb-3">
          <label>Jenjang</label>
          <select name="jenjang" id="jenjang" class="form-select" required onchange="updateKelasOptions()">
            <option value="">-- Pilih Jenjang --</option>
<<<<<<< HEAD
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
=======
            <?php
              $jenjang_opsi = ['SD', 'SMP', 'SMA'];
              foreach ($jenjang_opsi as $jenjang_item) {
                $selected = ($edit_data['jenjang'] ?? '') === $jenjang_item ? 'selected' : '';
                echo "<option value='$jenjang_item' $selected>$jenjang_item</option>";
              }
            ?>
>>>>>>> origin
          </select>
        </div>

        <div class="mb-3">
          <label>Kelas</label>
          <select name="kelas" id="kelas" class="form-select" required>
<<<<<<< HEAD
            <option value="">-- Pilih Kelas --</option>
=======
            <?php if (!empty($edit_data['kelas'])): ?>
              <option selected value="<?= $edit_data['kelas'] ?>">Kelas <?= $edit_data['kelas'] ?></option>
            <?php else: ?>
              <option value="">-- Pilih Kelas --</option>
            <?php endif; ?>
>>>>>>> origin
          </select>
        </div>

        <div class="mb-3">
<<<<<<< HEAD
          <label>Durasi</label>
          <div class="input-group">
            <input type="number" name="durasi" class="form-control" required>
            <select name="satuan_durasi" class="form-select" required>
              <option value="bulan">Bulan</option>
              <option value="tahun">Tahun</option>
=======
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
>>>>>>> origin
            </select>
          </div>
        </div>

        <div class="mb-3">
<<<<<<< HEAD
          <label>Status</label>
          <select name="status" class="form-select" required>
            <option value="aktif">Aktif</option>
            <option value="expired">Expired</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary">Tambah Paket</button>
      </form>
    </div>
  </div>
=======
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
          <td><?= htmlspecialchars($row['status']) ?></td>
          <td>
            <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus paket ini?')">Hapus</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
>>>>>>> origin
</div>

<script>
function updateKelasOptions() {
  const jenjang = document.getElementById('jenjang').value;
  const kelasSelect = document.getElementById('kelas');
<<<<<<< HEAD
  let options = [];

  if (jenjang === 'SD') options = ['1', '2', '3', '4', '5', '6'];
  else if (jenjang === 'SMP') options = ['7', '8', '9'];
  else if (jenjang === 'SMA') options = ['10', '11', '12'];
=======

  let options = [];
  if (jenjang === 'SD') {
    options = ['1','2','3','4','5','6'];
  } else if (jenjang === 'SMP') {
    options = ['7','8','9'];
  } else if (jenjang === 'SMA') {
    options = ['10','11','12'];
  }
>>>>>>> origin

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
