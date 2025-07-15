<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'siswa') {
  header('Location: ../index.php');
  exit;
}
include '../includes/header.php';
include '../config/database.php';

// Ambil username
$username = $_SESSION['user']['username'];

// Cek langganan aktif
$query = "SELECT paket FROM langganan WHERE username = '$username' AND status = 'aktif' LIMIT 1";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$paket = $row['paket'] ?? 'none';
?>

<div class="container mt-5">
  <h3 class="fw-bold">Kelas Online</h3>

  <?php if ($paket === 'premium'): ?>
    <p class="mb-4">Berikut adalah jadwal kelas online (Zoom / Google Meet) yang bisa kamu ikuti:</p>

    <table class="table table-bordered table-hover">
      <thead class="table-primary">
        <tr>
          <th>Hari</th>
          <th>Jam</th>
          <th>Tutor</th>
          <th>Topik</th>
          <th>Link</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Senin</td>
          <td>19.00 - 20.00</td>
          <td>Bapak Dimas</td>
          <td>Matematika Dasar</td>
          <td><a href="https://zoom.us/sample-link" target="_blank">Join</a></td>
        </tr>
        <tr>
          <td>Rabu</td>
          <td>18.30 - 19.30</td>
          <td>Ibu Rani</td>
          <td>Bahasa Inggris</td>
          <td><a href="https://meet.google.com/sample-link" target="_blank">Join</a></td>
        </tr>
      </tbody>
    </table>

  <?php else: ?>
    <div class="alert alert-warning mt-4">
      <h5 class="mb-2"><i class="bi bi-lock-fill me-1"></i> Fitur Terkunci</h5>
      <p>Fitur <strong>Kelas Online</strong> hanya tersedia untuk pengguna dengan <strong>paket Premium</strong>.</p>
      <a href="../langganan/paket.php" class="btn btn-primary mt-2">Upgrade ke Premium</a>
    </div>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
