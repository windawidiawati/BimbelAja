<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  $role     = $_POST['role']; // siswa, tutor

  // Cek apakah username sudah ada
  $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
  mysqli_stmt_bind_param($stmt, "s", $username);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if (mysqli_num_rows($result) > 0) {
    $error = "Username sudah terdaftar!";
  } else {
    // Simpan user baru
    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $username, $password, $role);
    mysqli_stmt_execute($stmt);
    header('Location: login.php');
    exit;
  }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
  <div class="card shadow-sm w-100" style="max-width: 450px;">
    <div class="card-body">
      <h4 class="card-title text-center mb-4"><i class="bi bi-person-plus-fill me-2"></i>Registrasi Akun</h4>

      <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= $error; ?></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 5 karakter" required minlength="5">
        </div>

        <div class="mb-4">
          <label for="role" class="form-label">Daftar Sebagai</label>
          <select name="role" id="role" class="form-select" required>
            <option value="siswa">Siswa</option>
            <option value="tutor">Tutor</option>
          </select>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Daftar Sekarang</button>
        </div>
      </form>

      <div class="text-center mt-3">
        <span>Sudah punya akun? <a href="login.php">Login di sini</a></span>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
