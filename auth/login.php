<?php
session_start();
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  // Gunakan prepared statement untuk keamanan
  $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username=? AND password=?");
  mysqli_stmt_bind_param($stmt, 'ss', $username, $password);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $user = mysqli_fetch_assoc($result);

  if ($user) {
    $_SESSION['user'] = $user;
    header('Location: ../' . $user['role'] . '/dashboard.php');
    exit;
  } else {
    $error = "Login gagal! Username atau password salah.";
  }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
  <div class="card shadow-sm w-100" style="max-width: 400px;">
    <div class="card-body">
      <h4 class="card-title text-center mb-4"><i class="bi bi-box-arrow-in-right me-2"></i>Login BimbelAja</h4>

      <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= $error; ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" name="username" class="form-control" id="username" placeholder="Masukkan username" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" name="password" class="form-control" id="password" placeholder="Masukkan password" required>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-in-right me-1"></i>Login</button>
        </div>
      </form>

      <div class="text-center mt-3">
        <span>Belum punya akun? <a href="register.php">Daftar di sini</a></span>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
