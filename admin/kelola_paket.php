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
?>

<div class="container mt-5">
  <h3 class="mb-4">Kelola Paket Langganan</h3>

  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">Tambah Paket Baru</div>
    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
          <label>Nama Paket</label>
          <input type="text" name="nama" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Jenjang</label>
          <select name="jenjang" id="jenjang" class="form-select" required onchange="updateKelasOptions()">
            <option value="">-- Pilih Jenjang --</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
          </select>
        </div>

        <div class="mb-3">
          <label>Kelas</label>
          <select name="kelas" id="kelas" class="form-select" required>
            <option value="">-- Pilih Kelas --</option>
          </select>
        </div>

        <div class="mb-3">
          <label>Durasi</label>
          <div class="input-group">
            <input type="number" name="durasi" class="form-control" required>
            <select name="satuan_durasi" class="form-select" required>
              <option value="bulan">Bulan</option>
              <option value="tahun">Tahun</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
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
</div>

<script>
function updateKelasOptions() {
  const jenjang = document.getElementById('jenjang').value;
  const kelasSelect = document.getElementById('kelas');
  let options = [];

  if (jenjang === 'SD') options = ['1', '2', '3', '4', '5', '6'];
  else if (jenjang === 'SMP') options = ['7', '8', '9'];
  else if (jenjang === 'SMA') options = ['10', '11', '12'];

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
