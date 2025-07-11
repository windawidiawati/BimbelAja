<?php
include '../includes/auth.php';
include '../includes/header.php';

// Hanya izinkan role siswa dan tutor
if ($_SESSION['user']['role'] !== 'siswa' && $_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php');
  exit;
}

$user = $_SESSION['user'];
?>

<div class="container mt-5">
  <div class="card shadow-sm">
    <div class="card-body">
      <h3 class="text-center mb-4">Profil Akun</h3>

      <table class="table table-bordered">
        <tr>
          <th>Username</th>
          <td><?= htmlspecialchars($user['username']); ?></td>
        </tr>
        <tr>
          <th>Role</th>
          <td><?= ucfirst($user['role']); ?></td>
        </tr>

        <?php if ($user['role'] === 'siswa'): ?>
          <!-- Info tambahan khusus siswa -->
          <tr>
            <th>Kelas</th>
            <td><?= htmlspecialchars($user['kelas'] ?? '-'); ?></td>
          </tr>
          <tr>
            <th>Jenjang Sekolah</th>
            <td><?= htmlspecialchars($user['jenjang'] ?? '-'); ?></td>
          </tr>
        <?php endif; ?>
      </table>

      <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Kembali</a>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
