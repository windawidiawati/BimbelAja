<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include '../config/database.php';

$error = '';
$usernameInput = '';
$allowedRoles = ['admin', 'siswa', 'tutor', 'kasir'];

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
          $sql = "SELECT u.*, k.nama_kelas
              FROM users u
              LEFT JOIN kelas k ON u.kelas_id = k.id
              WHERE u.username = ?";
      $stmt = mysqli_prepare($conn, $sql);
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
            header('Location: ../langganan/paket.php');
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
   <style>
    :root {
      --primary: #0d6efd;
      --primary-light: #3d8bfd;
      --primary-dark: #0b5ed7;
      --secondary: #f8f9fa;
      --light: #f8f9fa;
      --dark: #212529;
      --gray: #6c757d;
    }

    body {
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      color: var(--dark);
      line-height: 1.6;
      /* Wavy gradient background */
      background: linear-gradient(135deg, 
        #f0f7ff 0%, 
        #d0e3ff 25%, 
        #a8c8ff 50%, 
        #7aadff 75%, 
        #0d6efd 100%);
      background-size: 400% 400%;
      animation: waveBackground 15s ease infinite;
    }

    @keyframes waveBackground {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .login-container {
      width: 100%;
      max-width: 420px;
      animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .login-card {
      border: none;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(5px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .login-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }

    .card-header {
      background: rgba(13, 110, 253, 0.1);
      color: var(--primary);
      text-align: center;
      padding: 2rem 1.5rem;
      border-bottom: 1px solid rgba(13, 110, 253, 0.1);
    }

    .brand-logo {
      font-size: 2.5rem;
      margin-bottom: 1rem;
      color: var(--primary);
      text-shadow: 0 2px 4px rgba(13, 110, 253, 0.2);
    }

    .login-title {
      font-weight: 700;
      font-size: 1.75rem;
      margin-bottom: 0.5rem;
    }

    .login-subtitle {
      font-weight: 400;
      color: var(--gray);
      font-size: 0.95rem;
    }

    .card-body {
      padding: 2rem;
      background: rgba(255, 255, 255, 0.8);
    }

    .form-label {
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 0.5rem;
      font-size: 0.95rem;
    }

    .input-group {
      margin-bottom: 1.5rem;
    }

    .input-group-text {
      background-color: rgba(13, 110, 253, 0.05);
      border: 1px solid rgba(13, 110, 253, 0.1);
      color: var(--primary);
    }

    .form-control {
      border-radius: 8px;
      padding: 10px 15px;
      border: 1px solid rgba(13, 110, 253, 0.2);
      transition: all 0.3s;
      background-color: rgba(255, 255, 255, 0.8);
    }

    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
      background-color: white;
    }

    .btn-login {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      border: none;
      border-radius: 8px;
      padding: 10px;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: all 0.3s;
      color: white;
    }

    .btn-login:hover {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
    }

    .alert {
      border-radius: 8px;
      border-left: 4px solid #dc3545;
      background-color: rgba(220, 53, 69, 0.1);
    }

    .login-footer {
      text-align: center;
      color: var(--gray);
      font-size: 0.9rem;
    }

    .login-footer a {
      color: var(--primary);
      font-weight: 600;
      text-decoration: none;
    }

    .login-footer a:hover {
      text-decoration: underline;
    }

    @media (max-width: 576px) {
      .card-body {
        padding: 1.5rem;
      }
      
      .login-title {
        font-size: 1.5rem;
      }
      
      .brand-logo {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card" style="width: 100%; max-width: 420px;">
      <div class="card-header">
        <div class="brand-logo">
          <i class="bi bi-book-half login-icon"></i>
        </div>
        <h1 class="login-title">BimbelAja</h1>
        <p class="mb-0">Masuk ke akun Anda</p>
      </div>
      <div class="card-body">
        <?php if (!empty($error)) : ?>
          <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
              <input type="text" id="username" name="username" class="form-control" 
                     value="<?= htmlspecialchars($usernameInput); ?>" 
                     placeholder="Masukkan username" required>
            </div>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
              <input type="password" id="password" name="password" class="form-control" 
                     placeholder="Masukkan password" required>
            </div>
          </div>

          <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary btn-login">
              <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </button>
          </div>
        </form>
        
  
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>