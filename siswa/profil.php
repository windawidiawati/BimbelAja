<?php
session_start();
include '../config/database.php';
$title = "Profil Siswa";
include '../includes/siswa_header_langganan.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['siswa', 'tutor'])) {
  header("Location: ../login.php");
  exit;
}
$id = $_SESSION['user']['id'];

$sql = "SELECT u.*, k.nama_kelas 
        FROM users u
        LEFT JOIN kelas k ON u.kelas_id = k.id
        WHERE u.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

$user = $_SESSION['user'];
$mode = $_GET['mode'] ?? 'lihat';
$success = $error = '';
?>

<div class="container mt-5">
  <div class="col-md-8 mx-auto">
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="mb-4 text-center"><i class="bi bi-person-circle me-2"></i>Profil Akun</h3>

        <!-- MODE: TAMPILKAN -->
        <?php if ($mode === 'lihat'): ?>
          <table class="table table-bordered">
            <tr><th>Nama</th><td><?= htmlspecialchars($user['nama']) ?></td></tr>
            <tr><th>Username</th><td><?= htmlspecialchars($user['username']) ?></td></tr>
            <tr><th>Role</th><td><?= ucfirst($user['role']) ?></td></tr>
            <?php if ($user['role'] === 'siswa'): ?>
              <tr><th>Jenjang</th><td><?= htmlspecialchars($user['jenjang']) ?></td></tr>
              <tr><th>Kelas</th><td><?= htmlspecialchars($_SESSION['user']['nama_kelas'] ?? '-') ?></td></tr>
            <?php else: ?>
              <tr><th>Keahlian</th><td><?= htmlspecialchars($user['keahlian']) ?></td></tr>
            <?php endif; ?>
          </table>

          <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
            <a href="?mode=edit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> Edit Profil</a>
            <a href="?mode=password" class="btn btn-info"><i class="bi bi-key"></i> Ganti Password</a>
            <?php if ($user['role'] === 'siswa'): ?>
              <a href="?mode=langganan" class="btn btn-outline-primary"><i class="bi bi-receipt"></i> Riwayat Langganan</a>
            <?php endif; ?>
            <!-- <a href="?mode=hapus" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus akun ini?')">
              <i class="bi bi-trash"></i> Hapus Akun
            </a> -->
            <a href="../auth/logout.php" class="btn btn-dark"><i class="bi bi-box-arrow-right"></i> Logout</a>

            <a href="dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Kembali</a>
          </div>

        <!-- MODE: EDIT PROFIL -->
        <?php elseif ($mode === 'edit'):
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);

    if ($user['role'] === 'siswa') {
      $kelas_id = (int) $_POST['kelas'];
      $jenjang = $_POST['jenjang'];
      $stmt = mysqli_prepare($conn, "UPDATE users SET nama=?, kelas_id=?, jenjang=? WHERE id=?");
      mysqli_stmt_bind_param($stmt, 'sisi', $nama, $kelas_id, $jenjang, $user['id']);
    } else {
      $keahlian = trim($_POST['keahlian']);
      $stmt = mysqli_prepare($conn, "UPDATE users SET nama=?, keahlian=? WHERE id=?");
      mysqli_stmt_bind_param($stmt, 'ssi', $nama, $keahlian, $user['id']);
    }

    if (mysqli_stmt_execute($stmt)) {
      $_SESSION['user'] = array_merge($user, [
        'nama' => $nama,
        'kelas_id' => $kelas_id ?? '',
        'jenjang' => $jenjang ?? '',
        'keahlian' => $keahlian ?? '',
      ]);
      header("Location: profil.php");
      exit;
    } else {
      $error = "Gagal menyimpan perubahan.";
    }
  }
?>

          <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
          <form method="POST">
            <div class="mb-3">
              <label>Nama Lengkap</label>
              <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required>
            </div>
            <?php if ($user['role'] === 'siswa'): ?>
              <div class="mb-3">
                <div class="mb-3">
  <label>Jenjang</label>
  <select name="jenjang" class="form-select" onchange="this.form.submit()" required>
    <option value="">-- Pilih Jenjang --</option>
    <option value="SD" <?= $user['jenjang'] === 'SD' ? 'selected' : '' ?>>SD</option>
    <option value="SMP" <?= $user['jenjang'] === 'SMP' ? 'selected' : '' ?>>SMP</option>
    <option value="SMA" <?= $user['jenjang'] === 'SMA' ? 'selected' : '' ?>>SMA</option>
  </select>
</div>
<div class="mb-3">
  <label>Kelas</label>
 <select name="kelas" class="form-select" required>
  <option value="">-- Pilih Kelas --</option>
  <?php
    if (!empty($user['jenjang'])) {
      $jenjang = mysqli_real_escape_string($conn, $user['jenjang']);
      $kelas = mysqli_query($conn, "SELECT id, nama_kelas FROM kelas WHERE jenjang = '$jenjang'");
      while ($k = mysqli_fetch_assoc($kelas)) {
        $selected = ($user['kelas_id'] == $k['id']) ? 'selected' : '';
        echo "<option value='{$k['id']}' $selected>{$k['nama_kelas']}</option>";
      }
    }
  ?>
</select>

</div>


            <?php else: ?>
              <div class="mb-3">
                <label>Keahlian</label>
                <input type="text" name="keahlian" class="form-control" value="<?= htmlspecialchars($user['keahlian']) ?>" required>
              </div>
            <?php endif; ?>
            <button class="btn btn-primary" type="submit">Simpan</button>
            <a href="profil.php" class="btn btn-secondary">Batal</a>
          </form>

        <!-- MODE: GANTI PASSWORD -->
        <?php elseif ($mode === 'password'):
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $old = $_POST['old_password'];
          $p1 = $_POST['password'];
          $p2 = $_POST['password2'];
          // Ambil hash password dari database
          $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id=?");
          mysqli_stmt_bind_param($stmt, 'i', $user['id']);
          mysqli_stmt_execute($stmt);
          mysqli_stmt_bind_result($stmt, $hashed);
          mysqli_stmt_fetch($stmt);
          mysqli_stmt_close($stmt);

    if (!password_verify($old, $hashed)) {
      $error = "Password lama salah!";
    } elseif ($p1 !== $p2) {
      $error = "Password baru tidak cocok!";
    } elseif (strlen($p1) < 6) {
      $error = "Password baru minimal 6 karakter.";
    } else {
      $hash = password_hash($p1, PASSWORD_DEFAULT);
      $stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
      mysqli_stmt_bind_param($stmt, 'si', $hash, $user['id']);
      mysqli_stmt_execute($stmt);
      $success = "Password berhasil diganti.";
    }
  }
?><?php if ($success): ?>
  <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>
          <form method="POST">
  <div class="mb-3">
    <label>Password Lama</label>
    <input type="password" name="old_password" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Password Baru</label>
    <input type="password" name="password" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Ulangi Password Baru</label>
    <input type="password" name="password2" class="form-control" required>
  </div>
  <button class="btn btn-primary" type="submit">Ganti Password</button>
  <a href="profil.php" class="btn btn-secondary">Batal</a>
</form>


       <!-- MODE: HAPUS AKUN -->
        <!-- <?php elseif ($mode === 'hapus'):
          $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id=?");
          mysqli_stmt_bind_param($stmt, 'i', $user['id']);
          mysqli_stmt_execute($stmt);
          session_destroy();
          header("Location: ../login.php");
          exit;
        ?> -->

        <!-- MODE: RIWAYAT LANGGANAN -->
         <?php elseif ($mode === 'langganan'):
          $result = $conn->query("SELECT * FROM pembayaran WHERE user_id = {$user['id']} ORDER BY tanggal DESC");
        ?>
          <h5>Riwayat Langganan Paket</h5>
          <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered">
              <thead>
                <tr><th>#</th><th>Paket</th><th>Harga</th><th>Status</th><th>Tanggal</th></tr>
              </thead>
              <tbody>
                <?php $i=1; while ($r = $result->fetch_assoc()): ?>
                  <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($r['paket']) ?></td>
                    <td>Rp <?= number_format($r['harga'], 0, ',', '.') ?></td>
                    <td><span class="badge bg-<?= $r['status'] === 'lunas' ? 'success' : ($r['status'] === 'pending' ? 'warning text-dark' : 'danger') ?>">
                      <?= ucfirst($r['status']) ?></span></td>
                    <td><?= date('d M Y, H:i', strtotime($r['tanggal'])) ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="alert alert-info">Belum ada data langganan.</div>
          <?php endif; ?>
          <a href="profil.php" class="btn btn-secondary">Kembali</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
// Isi ulang saat reload (mode edit)
window.addEventListener('DOMContentLoaded', () => {
  const currentJenjang = "<?= $user['jenjang'] ?? '' ?>";
  const currentKelas = "<?= $user['kelas'] ?? '' ?>";
  const kelasSelect = document.getElementById('kelas');
  if (kelasOptions[currentJenjang]) {
    kelasOptions[currentJenjang].forEach(function (kelas) {
      const option = document.createElement('option');
      option.value = kelas;
      option.textContent = kelas;
      if (kelas === currentKelas) option.selected = true;
      kelasSelect.appendChild(option);
    });
  }
});
</script>

<?php include '../includes/footer.php'; ?>
