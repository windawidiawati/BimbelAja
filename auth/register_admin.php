<?php
session_start();
include '../config/database.php';

$error = '';
$success = '';
$username = '';
$nama = '';

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  $nama     = trim($_POST['nama']);

  if (empty($username) || empty($password) || empty($nama)) {
    $error = "Semua field wajib diisi!";
  } else {
    // Cek apakah username sudah ada
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
      $error = "Username sudah digunakan!";
    } else {
      $hashed = password_hash($password, PASSWORD_DEFAULT);
      $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role, nama) VALUES (?, ?, 'admin', ?)");
      mysqli_stmt_bind_param($stmt, 'sss', $username, $hashed, $nama);
      if (mysqli_stmt_execute($stmt)) {
        $success = "Admin berhasil didaftarkan!";
        $username = '';
        $nama = '';
      } else {
        $error = "Terjadi kesalahan saat menyimpan data admin.";
      }
    }
  }
}
?>

<!-- TAMPILAN HTML -->
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Register Admin - BimbelAja</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow w-100" style="max-width: 500px;">
      <div class="card-body">
        <h4 class="card-title text-center mb-4">
          <i class="bi bi-person-plus-fill me-2"></i>Register Admin
        </h4>

        <?php if (!empty($error)) : ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
          <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
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
            <input type="password" name="password" class="form-control" required>
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Daftar Admin</button>
          </div>
        </form>

        <div class="text-center mt-3">
          <a href="login.php">Kembali ke Login</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
