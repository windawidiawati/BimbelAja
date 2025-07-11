// File: register_siswa.php
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
          <label class="form-label">Kelas</label>
          <input type="text" name="kelas" class="form-control" value="<?= htmlspecialchars($kelas); ?>" required>
        </div>

        <div class="mb-4">
          <label class="form-label">Jenjang</label>
          <select name="jenjang" class="form-select" required>
            <option value="">-- Pilih Jenjang --</option>
            <option value="SD" <?= $jenjang === 'SD' ? 'selected' : '' ?>>SD</option>
            <option value="SMP" <?= $jenjang === 'SMP' ? 'selected' : '' ?>>SMP</option>
            <option value="SMA" <?= $jenjang === 'SMA' ? 'selected' : '' ?>>SMA</option>
            <option value="SMK" <?= $jenjang === 'SMK' ? 'selected' : '' ?>>SMK</option>
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

<?php include '../includes/footer.php'; ?>
