<?php
session_start();
include '../config/database.php';
include '../includes/header.php';

if ($_SESSION['user']['role'] !== 'admin') {
  header("Location: ../index.php");
  exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = $_POST['nama'] ?? '';
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  $role = $_POST['role'] ?? '';
  $jenjang = $_POST['jenjang'] ?? '';
  $kelas = $_POST['kelas'] ?? '';
  $keahlian = $_POST['keahlian'] ?? '';

  if (!$nama || !$username || !$password || !$role) {
    $error = "Semua field wajib diisi.";
  } else {
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    if ($role === 'siswa') {
      $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role, jenjang, kelas) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("ssssss", $nama, $username, $hashed, $role, $jenjang, $kelas);
    } elseif ($role === 'tutor') {
      $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role, keahlian) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("sssss", $nama, $username, $hashed, $role, $keahlian);
    } else {
      $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $nama, $username, $hashed, $role);
    }

    if ($stmt->execute()) {
      $success = "User berhasil ditambahkan.";
    } else {
      $error = "Gagal menambahkan user.";
    }
  }
}
?>

<div class="container mt-5">
  <h3>Tambah User Baru</h3>
  <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
  <form method="POST">
    <div class="mb-3">
      <label>Nama</label>
      <input type="text" name="nama" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Username</label>
      <input type="text" name="username" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Role</label>
      <select name="role" class="form-select" required onchange="handleRoleChange(this.value)">
        <option value="">-- Pilih Role --</option>
        <option value="admin">Admin</option>
        <option value="siswa">Siswa</option>
        <option value="tutor">Tutor</option>
      </select>
    </div>
    <div class="mb-3" id="siswa-fields" style="display: none;">
      <label>Jenjang</label>
      <select name="jenjang" id="jenjang" class="form-select" onchange="updateKelasOptions()">
        <option value="">-- Pilih Jenjang --</option>
        <option value="SD">SD</option>
        <option value="SMP">SMP</option>
        <option value="SMA">SMA</option>
      </select>
      <label class="mt-2">Kelas</label>
      <select name="kelas" id="kelas" class="form-select">
        <option value="">-- Pilih Kelas --</option>
      </select>
    </div>
    <div class="mb-3" id="tutor-fields" style="display: none;">
      <label>Keahlian</label>
      <input type="text" name="keahlian" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Tambah</button>
  </form>
</div>

<script>
function handleRoleChange(role) {
  document.getElementById('siswa-fields').style.display = role === 'siswa' ? 'block' : 'none';
  document.getElementById('tutor-fields').style.display = role === 'tutor' ? 'block' : 'none';
}

function updateKelasOptions() {
  const jenjang = document.getElementById('jenjang').value;
  const kelasSelect = document.getElementById('kelas');
  let options = [];

  if (jenjang === 'SD') options = ['1','2','3','4','5','6'];
  else if (jenjang === 'SMP') options = ['7','8','9'];
  else if (jenjang === 'SMA') options = ['10','11','12'];

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
