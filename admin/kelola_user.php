<?php
session_start();
include '../config/database.php';
include '../includes/header.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Proses hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $id");
    header("Location: kelola_user.php");
    exit;
}

// Filter
$filter_role = $_GET['role'] ?? '';
$filter_jenjang = $_GET['jenjang'] ?? '';
$filter_kelas = $_GET['kelas'] ?? '';
$filter_keahlian = $_GET['keahlian'] ?? '';

// Query
$sql = "SELECT * FROM users WHERE 1=1";
if ($filter_role) {
    $sql .= " AND role = '$filter_role'";
    if ($filter_role === 'siswa' && $filter_jenjang) {
        $sql .= " AND jenjang = '$filter_jenjang'";
        if ($filter_kelas) {
            $sql .= " AND kelas = '$filter_kelas'";
        }
    }
    if ($filter_role === 'tutor' && $filter_keahlian) {
        $sql .= " AND keahlian LIKE '%$filter_keahlian%'";
    }
}
$sql .= " ORDER BY nama ASC";
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Kelola User</h3>
    <a href="/BimbelAja/admin/tambah_user.php" class="btn btn-primary">+ Tambah User</a>
    <?php if ($user['role'] === 'siswa'): ?>
      <a href="kelola_siswa.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-info">Detail</a>
<?php endif; ?>

  </div>

  <!-- Form Filter -->
  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-3">
      <label class="form-label">Role</label>
      <select name="role" class="form-select" onchange="this.form.submit()">
        <option value="">-- Semua --</option>
        <option value="siswa" <?= $filter_role == 'siswa' ? 'selected' : '' ?>>Siswa</option>
        <option value="tutor" <?= $filter_role == 'tutor' ? 'selected' : '' ?>>Tutor</option>
        <option value="admin" <?= $filter_role == 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
    </div>

    <?php if ($filter_role === 'siswa'): ?>
      <div class="col-md-3">
        <label class="form-label">Jenjang</label>
        <select name="jenjang" class="form-select" onchange="this.form.submit()">
          <option value="">-- Pilih Jenjang --</option>
          <?php foreach (['SD','SMP','SMA'] as $j): ?>
            <option value="<?= $j ?>" <?= $filter_jenjang == $j ? 'selected' : '' ?>><?= $j ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Kelas</label>
        <select name="kelas" class="form-select" <?= $filter_jenjang ? '' : 'disabled' ?> onchange="this.form.submit()">
          <option value="">-- Pilih Kelas --</option>
          <?php
            $kelas_opsi = [];
            if ($filter_jenjang === 'SD') $kelas_opsi = ['1','2','3','4','5','6'];
            if ($filter_jenjang === 'SMP') $kelas_opsi = ['7','8','9'];
            if ($filter_jenjang === 'SMA') $kelas_opsi = ['10','11','12'];
            foreach ($kelas_opsi as $k):
          ?>
            <option value="<?= $k ?>" <?= $filter_kelas == $k ? 'selected' : '' ?>>Kelas <?= $k ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php elseif ($filter_role === 'tutor'): ?>
      <div class="col-md-3">
        <label class="form-label">Keahlian</label>
        <input type="text" name="keahlian" class="form-control" placeholder="Contoh: Matematika" value="<?= htmlspecialchars($filter_keahlian) ?>" onchange="this.form.submit()">
      </div>
    <?php endif; ?>
  </form>

  <!-- Tabel -->
  <table class="table table-bordered table-hover">
    <thead class="table-light">
      <tr>
        <th>Nama</th>
        <th>Username</th>
        <th>Role</th>
        <th>Jenjang</th>
        <th>Kelas</th>
        <th>Keahlian</th>
        <th width="120px">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while($user = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= htmlspecialchars($user['nama']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td><?= $user['role'] === 'siswa' ? $user['jenjang'] : '-' ?></td>
            <td><?= $user['role'] === 'siswa' ? $user['kelas'] : '-' ?></td>
            <td><?= $user['role'] === 'tutor' ? $user['keahlian'] : '-' ?></td>
            <td>
              <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
              <a href="?hapus=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="7" class="text-center">Tidak ada data user.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include '../includes/footer.php'; ?>
