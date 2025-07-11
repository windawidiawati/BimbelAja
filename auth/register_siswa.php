<?php
include '../config/database.php';

$username = $password = $nama = $kelas = $jenjang = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  $nama     = trim($_POST['nama']);
  $kelas    = trim($_POST['kelas']);
  $jenjang  = trim($_POST['jenjang']);
  $role     = 'siswa';

  if (empty($username) || empty($password) || empty($nama) || empty($kelas) || empty($jenjang)) {
    $error = "Semua field wajib diisi!";
  } else {
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
      $error = "Username sudah terdaftar!";
    } else {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);

      $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role, nama, kelas, jenjang) VALUES (?, ?, ?, ?, ?, ?)");
      mysqli_stmt_bind_param($stmt, "ssssss", $username, $hashed_password, $role, $nama, $kelas, $jenjang);
      mysqli_stmt_execute($stmt);

      header('Location: login.php');
      exit;
    }
  }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
  <div class="card shadow-sm w-100" style="max-width: 500px;">
    <div class="card-body">
      <h4 class="card-title text-center mb-4"><i class="bi bi-person-plus-fill me-2"></i>Registrasi Siswa</h4>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($nama); ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username); ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required minlength="5">
        </div>

        <div class="mb-3">
          <label class="form-label">Jenjang</label>
          <select name="jenjang" id="jenjang" class="form-select" required>
            <option value="">-- Pilih Jenjang --</option>
            <option value="SD" <?= $jenjang === 'SD' ? 'selected' : '' ?>>SD</option>
            <option value="SMP" <?= $jenjang === 'SMP' ? 'selected' : '' ?>>SMP</option>
            <option value="SMA" <?= $jenjang === 'SMA' ? 'selected' : '' ?>>SMA</option>
          </select>
        </div>

        <div class="mb-4">
          <label class="form-label">Kelas</label>
          <select name="kelas" id="kelas" class="form-select" required>
            <option value="">-- Pilih Kelas --</option>
          </select>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Daftar Siswa</button>
        </div>
      </form>

      <div class="text-center mt-3">
        <span>Sudah punya akun? <a href="login.php">Login di sini</a></span>
      </div>
    </div>
  </div>
</div>

<script>
const kelasOptions = {
  SD: ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'],
  SMP: ['Kelas 7', 'Kelas 8', 'Kelas 9'],
  SMA: ['Kelas 10', 'Kelas 11', 'Kelas 12']
};

document.getElementById('jenjang').addEventListener('change', function () {
  const jenjang = this.value;
  const kelasSelect = document.getElementById('kelas');
  kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';

  if (kelasOptions[jenjang]) {
    kelasOptions[jenjang].forEach(function (kelas) {
      const option = document.createElement('option');
      option.value = kelas;
      option.textContent = kelas;
      kelasSelect.appendChild(option);
    });
  }
});

// Saat reload, isi kelas kembali jika user submit gagal
window.addEventListener('DOMContentLoaded', () => {
  const currentJenjang = '<?= $jenjang ?>';
  const currentKelas = '<?= $kelas ?>';
  if (currentJenjang && kelasOptions[currentJenjang]) {
    const kelasSelect = document.getElementById('kelas');
    kelasOptions[currentJenjang].forEach(k => {
      const option = document.createElement('option');
      option.value = k;
      option.textContent = k;
      if (k === currentKelas) option.selected = true;
      kelasSelect.appendChild(option);
    });
  }
});
</script>

<?php include '../includes/footer.php'; ?>
