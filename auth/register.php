<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];
  $role     = $_POST['role']; // siswa, tutor

  $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
  if (mysqli_num_rows($cek) > 0) {
    $error = "Username sudah terdaftar!";
  } else {
    mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')");
    header('Location: login.php');
  }
}
?>

<?php include '../includes/header.php'; ?>
<div class="container mt-5">
  <h2>Register</h2>
  <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
  <form method="POST">
    <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
    <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
    <select name="role" class="form-control mb-2">
      <option value="siswa">Siswa</option>
      <option value="tutor">Tutor</option>
    </select>
    <button type="submit" class="btn btn-success">Daftar</button>
  </form>
</div>
<?php include '../includes/footer.php'; ?>
