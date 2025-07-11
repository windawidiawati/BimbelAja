<?php
session_start();
include '../config/database.php';

$error = '';
$usernameInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $usernameInput = trim($_POST['username']);
  $password = trim($_POST['password']);

  if (empty($usernameInput) || empty($password)) {
    $error = "Harap isi semua field!";
  } else {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, 's', $usernameInput);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
      if (password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header('Location: ../' . $user['role'] . '/dashboard.php');
        exit;
      } else {
        $error = "Password salah!";
      }
    } else {
      $error = "Username tidak ditemukan!";
    }
  }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
  <div class="card shadow w-100" style="max-width: 420px;">
    <div class="card-body">
      <h4 class="card-title text-center mb-4">
        <i class="bi bi-box-arrow-in-right me-2"></i>Login BimbelAja
      </h4>

      <?php if (!empty($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($usernameInput); ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-box-arrow-in-right me-1"></i>Login
          </button>
        </div>
      </form>

      <div class="text-center mt-3">
        <span>Belum punya akun?
          <a href="register_siswa.php">Daftar Siswa</a> |
          <a href="register_tutor.php">Daftar Tutor</a>
        </span>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
