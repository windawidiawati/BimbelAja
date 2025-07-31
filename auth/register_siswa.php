<?php
include '../config/database.php';

$username = $password = $nama = $kelas = $jenjang = $email = $no_hp = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  $nama     = trim($_POST['nama']);
  $kelas_id = intval($_POST['kelas_id'] ?? 0);
  $jenjang  = trim($_POST['jenjang']);
  $email    = trim($_POST['email']);
  $no_hp    = trim($_POST['no_hp']);
  $role     = 'siswa';
  $status   = 'belum_aktif';

  if (empty($username) || empty($password) || empty($nama) || empty($kelas) || empty($jenjang) || empty($email) || empty($no_hp)) {
    $error = "Semua field wajib diisi!";
  } else {
    // Cek username/email sudah terdaftar
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
      $error = "Username atau Email sudah terdaftar!";
    } else {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);

      $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role, nama, kelas_id, jenjang, email, no_hp, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssssissss", $username, $hashed_password, $role, $nama, $kelas_id, $jenjang, $email, $no_hp, $status);
;
      mysqli_stmt_bind_param($stmt, "sssssssss", $username, $hashed_password, $role, $nama, $kelas, $jenjang, $email, $no_hp, $status);
      mysqli_stmt_execute($stmt);

      header('Location: login.php?msg=register-success');
      exit;
    }
  }
}

?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email); ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">No. HP / WhatsApp</label>
          <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($no_hp); ?>" required>
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
          <select name="kelas_id" class="form-select" required>
  <option value="">-- Pilih Kelas --</option>
  <?php
  $res = mysqli_query($conn, "SELECT * FROM kelas ORDER BY jenjang, nama_kelas");
  while ($row = mysqli_fetch_assoc($res)) {
    $selected = ($row['id'] == ($_POST['kelas_id'] ?? '')) ? 'selected' : '';
    echo "<option value='{$row['id']}' $selected>{$row['nama_kelas']} ({$row['jenjang']})</option>";
  }
  ?>
</select>

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
  SD: ['1', '2', '3', '4', '5', '6'],
  SMP: ['7', '8', '9'],
  SMA: ['10 IPA', '10 IPS', '11 IPA', '11 IPS', '12 IPA', '12 IPS']
};

document.getElementById('jenjang').addEventListener('change', function () {
  const jenjang = this.value;
  const kelasSelect = document.getElementById('kelas');
  kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';

  if (kelasOptions[jenjang]) {
    kelasOptions[jenjang].forEach(function (kelas) {
      const option = document.createElement('option');
      option.value = kelas;
      option.textContent = "Kelas " + kelas;
      kelasSelect.appendChild(option);
    });
  }
});

window.addEventListener('DOMContentLoaded', () => {
  const currentJenjang = '<?= $jenjang ?>';
  const currentKelas = '<?= $kelas ?>';
  if (currentJenjang && kelasOptions[currentJenjang]) {
    const kelasSelect = document.getElementById('kelas');
    kelasOptions[currentJenjang].forEach(k => {
      const option = document.createElement('option');
      option.value = k;
      option.textContent = "Kelas " + k;
      if (k === currentKelas) option.selected = true;
      kelasSelect.appendChild(option);
    });
  }
});
</script>
