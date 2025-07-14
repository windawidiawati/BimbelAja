<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include '../config/database.php';

$error = '';
$usernameInput = '';
$allowedRoles = ['admin', 'siswa', 'tutor'];

// Redirect jika sudah login
if (isset($_SESSION['user'])) {
  $userRole = $_SESSION['user']['role'];

  if ($userRole === 'siswa') {
    $userId = $_SESSION['user']['id'];
    $check = mysqli_query($conn, "SELECT * FROM langganan WHERE user_id = $userId LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
      header('Location: ../siswa/dashboard.php');
    } else {
      header('Location: ../index.php');
    }
  } else {
    header("Location: ../$userRole/dashboard.php");
  }
  exit;
}

// Inisialisasi login_attempts
if (!isset($_SESSION['login_attempts'])) {
  $_SESSION['login_attempts'] = 0;
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $usernameInput = trim($_POST['username']);
  $password = trim($_POST['password']);

  if (empty($usernameInput) || empty($password)) {
    $error = "Harap isi semua field!";
  } elseif ($_SESSION['login_attempts'] >= 5) {
    $error = "Terlalu banyak percobaan login. Coba lagi nanti.";
  } else {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, 's', $usernameInput);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
      if (!in_array($user['role'], $allowedRoles)) {
        $error = "Role tidak dikenali!";
      } elseif (password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        $_SESSION['login_attempts'] = 0;

        if ($user['role'] === 'siswa') {
          $userId = $user['id'];
          $check = mysqli_query($conn, "SELECT * FROM langganan WHERE user_id = $userId LIMIT 1");
          if (mysqli_num_rows($check) > 0) {
            header('Location: ../siswa/dashboard.php');
          } else {
            header('Location: ../index.php');
          }
        } else {
          header('Location: ../' . $user['role'] . '/dashboard.php');
        }
        exit;
      } else {
        $_SESSION['login_attempts']++;
        $error = "Password salah!";
      }
    } else {
      $_SESSION['login_attempts']++;
      $error = "Username tidak ditemukan!";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login BimbelAja</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
  <div class="container d-flex justify-content-center align-items-center" style="min-height: 90vh;">
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
</body>
</html>
