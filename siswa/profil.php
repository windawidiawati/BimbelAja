<?php
session_start();
include '../config/database.php';
include '../includes/header.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['siswa', 'tutor'])) {
  header("Location: ../login.php");
  exit;
}

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
              <tr><th>Kelas</th><td><?= htmlspecialchars($user['kelas']) ?></td></tr>
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
            <a href="?mode=hapus" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus akun ini?')">
              <i class="bi bi-trash"></i> Hapus Akun
            </a>
            <a href="dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Kembali</a>
          </div>

        <!-- MODE: EDIT PROFIL -->
        <?php elseif ($mode === 'edit'):
          if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = trim($_POST['nama']);
            if ($user['role'] === 'siswa') {
              $kelas = trim($_POST['kelas']);
              $jenjang = trim($_POST['jenjang']);
              $stmt = mysqli_prepare($conn, "UPDATE users SET nama=?, kelas=?, jenjang=? WHERE id=?");
              mysqli_stmt_bind_param($stmt, 'sssi', $nama, $kelas, $jenjang, $user['id']);
            } else {
              $keahlian = trim($_POST['keahlian']);
              $stmt = mysqli_prepare($conn, "UPDATE users SET nama=?, keahlian=? WHERE id=?");
              mysqli_stmt_bind_param($stmt, 'ssi', $nama, $keahlian, $user['id']);
            }
            if (mysqli_stmt_execute($stmt)) {
              $_SESSION['user'] = array_merge($user, [
                'nama' => $nama,
                'kelas' => $kelas ?? '',
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
                <label>Jenjang</label>
                <select name="jenjang" id="jenjang" class="form-select" required>
                  <option value="">-- Pilih Jenjang --</option>
                  <option value="SD" <?= $user['jenjang'] === 'SD' ? 'selected' : '' ?>>SD</option>
                  <option value="SMP" <?= $user['jenjang'] === 'SMP' ? 'selected' : '' ?>>SMP</option>
                  <option value="SMA" <?= $user['jenjang'] === 'SMA' ? 'selected' : '' ?>>SMA</option>
                </select>
              </div>
              <div class="mb-3">
                <label>Kelas</label>
                <select name="kelas" id="kelas" class="form-select" required>
                  <option value="">-- Pilih Kelas --</option>
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
            $p1 = $_POST['password'];
            $p2 = $_POST['password2'];
            if ($p1 !== $p2) {
              $error = "Password tidak cocok!";
            } elseif (strlen($p1) < 6) {
              $error = "Password minimal 6 karakter.";
            } else {
              $hash = password_hash($p1, PASSWORD_DEFAULT);
              $stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
              mysqli_stmt_bind_param($stmt, 'si', $hash, $user['id']);
              mysqli_stmt_execute($stmt);
              $success = "Password berhasil diganti.";
            }
          }
        ?>
          <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
          <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
          <form method="POST">
            <div class="mb-3">
              <label>Password Baru</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Ulangi Password</label>
              <input type="password" name="password2" class="form-control" required>
            </div>
            <button class="btn btn-primary" type="submit">Ganti Password</button>
            <a href="profil.php" class="btn btn-secondary">Batal</a>
          </form>

        <!-- MODE: HAPUS AKUN -->
        <?php elseif ($mode === 'hapus'):
          $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id=?");
          mysqli_stmt_bind_param($stmt, 'i', $user['id']);
          mysqli_stmt_execute($stmt);
          session_destroy();
          header("Location: ../login.php");
          exit;
        ?>

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
const kelasOptions = {
  SD: ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'],
  SMP: ['Kelas 7', 'Kelas 8', 'Kelas 9'],
  SMA: ['Kelas 10', 'Kelas 11', 'Kelas 12']
};

document.getElementById('jenjang')?.addEventListener('change', function () {
  const jenjang = this.value;
  const kelasSelect = document.getElementById('kelas');
  kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
  if (kelasOptions[jenjang]) {
    kelasOptions[jenjang].forEach(function (kelas) {
      const option = document.createElement('option');
      option.value = kelas;
      option.textContent = kelas;
      kelasSelect.appendChild(option);
    });
  }
});

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
