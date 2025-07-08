<?php
session_start();
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];
  
  $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
  $user = mysqli_fetch_assoc($query);

  if ($user) {
    $_SESSION['user'] = $user;
    header('Location: ../' . $user['role'] . '/dashboard.php');
  } else {
    $error = "Login gagal! Username atau password salah.";
  }
}
?>

<?php include '../includes/header.php'; ?>
<div class="container mt-5">
  <h2>Login</h2>
  <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
  <form method="POST">
    <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
    <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
    <button type="submit" class="btn btn-primary">Login</button>
  </form>
</div>
<?php include '../includes/footer.php'; ?>
