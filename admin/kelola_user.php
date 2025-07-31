<?php
ob_start();
include '../config/database.php';
include '../includes/admin_header.php';

if ($_SESSION['user']['role'] !== 'admin') {
  header("Location: ../index.php");
  exit;
}

// Hapus
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  mysqli_query($conn, "DELETE FROM users WHERE id = $id");
  header("Location: kelola_user.php");
  exit;
}

// Tambah user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
  $nama = $_POST['nama'] ?? '';
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  $role = $_POST['role'] ?? '';
  $jenjang = $_POST['jenjang'] ?? '';
  $kelas = $_POST['kelas'] ?? '';
  $keahlian = $_POST['keahlian'] ?? '';

  if ($nama && $username && $password && $role) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    if ($role === 'siswa') {
      $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role, jenjang, kelas) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("ssssss", $nama, $username, $hashed, $role, $jenjang, $kelas);
    } elseif ($role === 'tutor') {
      $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role, keahlian) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("sssss", $nama, $username, $hashed, $role, $keahlian);
    } else {
      $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $nama, $username, $hashed, $role);
    }

    $stmt->execute();
    header("Location: kelola_user.php");
    exit;
  }
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

<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Kelola User</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUser">+ Tambah User</button>
  </div>

  <!-- Filter -->
  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
      <select name="role" class="form-select" onchange="this.form.submit()">
        <option value="">-- Semua Role --</option>
        <option value="siswa" <?= $filter_role == 'siswa' ? 'selected' : '' ?>>Siswa</option>
        <option value="tutor" <?= $filter_role == 'tutor' ? 'selected' : '' ?>>Tutor</option>
        <option value="admin" <?= $filter_role == 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="kasir" <?= $filter_role == 'kasir' ? 'selected' : '' ?>>Kasir</option>
      </select>
    </div>

    <?php if ($filter_role === 'siswa'): ?>
      <div class="col-md-3">
        <select name="jenjang" class="form-select" onchange="this.form.submit()">
          <option value="">-- Pilih Jenjang --</option>
          <?php foreach (['SD','SMP','SMA'] as $j): ?>
            <option value="<?= $j ?>" <?= $filter_jenjang == $j ? 'selected' : '' ?>><?= $j ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
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
        <input type="text" name="keahlian" class="form-control" placeholder="Keahlian..." value="<?= htmlspecialchars($filter_keahlian) ?>" onchange="this.form.submit()">
      </div>
    <?php endif; ?>
  </form>

  <!-- Table -->
  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Nama</th>
          <th>Username</th>
          <th>Role</th>
          <th>Jenjang</th>
          <th>Kelas</th>
          <th>Keahlian</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
          <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?= htmlspecialchars($row['nama']) ?></td>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td><?= $row['role'] ?></td>
              <td><?= $row['role'] === 'siswa' ? $row['jenjang'] : '-' ?></td>
              <td><?= $row['role'] === 'siswa' ? $row['kelas'] : '-' ?></td>
              <td><?= $row['role'] === 'tutor' ? $row['keahlian'] : '-' ?></td>
              <td>
                <?php if ($row['role'] === 'siswa'): ?>
                  <a href="kelola_siswa.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Detail</a>
                <?php endif; ?>
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditUser" onclick='editUser(<?= json_encode($row) ?>)'>Edit</button>
                <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center">Tidak ada data user.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalUser" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Tambah User Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="add_user" value="1" />
          <div class="mb-3"><label>Nama</label><input type="text" name="nama" class="form-control" required></div>
          <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div>
          <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
          <div class="mb-3">
            <label>Role</label>
            <select name="role" class="form-select" onchange="handleRoleChange(this.value)" required>
              <option value="">-- Pilih Role --</option>
              <option value="admin">Admin</option>
              <option value="siswa">Siswa</option>
              <option value="tutor">Tutor</option>
              <option value="kasir">Kasir</option>
            </select>
          </div>

          <div id="siswa-fields" style="display: none;">
            <label>Jenjang</label>
            <select name="jenjang" id="jenjang" class="form-select" onchange="updateKelasOptions()">
              <option value="">-- Pilih Jenjang --</option>
              <option value="SD">SD</option>
              <option value="SMP">SMP</option>
              <option value="SMA">SMA</option>
            </select>
            <label class="mt-2">Kelas</label>
            <select name="kelas" id="kelas" class="form-select"></select>
          </div>

          <div class="mb-3" id="tutor-fields" style="display: none;">
            <label>Keahlian</label>
            <input type="text" name="keahlian" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Tambah</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="modalEditUser" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="update_user.php">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit-id">
          <div class="mb-3"><label>Nama</label><input type="text" name="nama" id="edit-nama" class="form-control" required></div>
          <div class="mb-3"><label>Username</label><input type="text" name="username" id="edit-username" class="form-control" required></div>
          <div class="mb-3"><label>Password (kosongkan jika tidak diubah)</label><input type="password" name="password" class="form-control"></div>
          <div class="mb-3">
            <label>Role</label>
            <select name="role" id="edit-role" class="form-select" onchange="handleEditRoleChange(this.value)">
              <option value="">-- Pilih Role --</option>
              <option value="admin">Admin</option>
              <option value="siswa">Siswa</option>
              <option value="tutor">Tutor</option>
              <option value="kasir">Kasir</option>
            </select>
          </div>
          <div id="edit-siswa-fields" style="display: none;">
            <label>Jenjang</label>
            <select name="jenjang" id="edit-jenjang" class="form-select" onchange="updateEditKelasOptions()">
              <option value="">-- Pilih Jenjang --</option>
              <option value="SD">SD</option>
              <option value="SMP">SMP</option>
              <option value="SMA">SMA</option>
            </select>
            <label class="mt-2">Kelas</label>
            <select name="kelas" id="edit-kelas" class="form-select"></select>
          </div>
          <div class="mb-3" id="edit-tutor-fields" style="display: none;">
            <label>Keahlian</label>
            <input type="text" name="keahlian" id="edit-keahlian" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JavaScript Section -->
<script>
function handleRoleChange(role) {
  document.getElementById('siswa-fields').style.display = role === 'siswa' ? 'block' : 'none';
  document.getElementById('tutor-fields').style.display = role === 'tutor' ? 'block' : 'none';
  updateKelasOptions();
}

function updateKelasOptions() {
  const jenjang = document.getElementById('jenjang').value;
  const kelasSelect = document.getElementById('kelas');
  let options = [];

  if (jenjang === 'SD') options = ['1','2','3','4','5','6'];
  else if (jenjang === 'SMP') options = ['7','8','9'];
  else if (jenjang === 'SMA') options = ['10','11','12'];

  kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
  options.forEach(k => {
    const opt = document.createElement('option');
    opt.value = k;
    opt.textContent = 'Kelas ' + k;
    kelasSelect.appendChild(opt);
  });
}

function editUser(data) {
  document.getElementById('edit-id').value = data.id;
  document.getElementById('edit-nama').value = data.nama;
  document.getElementById('edit-username').value = data.username;
  document.getElementById('edit-role').value = data.role;
  handleEditRoleChange(data.role);

  if (data.role === 'siswa') {
    document.getElementById('edit-jenjang').value = data.jenjang;
    updateEditKelasOptions();
    setTimeout(() => {
      document.getElementById('edit-kelas').value = data.kelas;
    }, 200);
  }

  if (data.role === 'tutor') {
    document.getElementById('edit-keahlian').value = data.keahlian;
  }
}

function handleEditRoleChange(role) {
  document.getElementById('edit-siswa-fields').style.display = role === 'siswa' ? 'block' : 'none';
  document.getElementById('edit-tutor-fields').style.display = role === 'tutor' ? 'block' : 'none';
}

function updateEditKelasOptions() {
  const jenjang = document.getElementById('edit-jenjang').value;
  const kelasSelect = document.getElementById('edit-kelas');
  let options = [];

  if (jenjang === 'SD') options = ['1','2','3','4','5','6'];
  else if (jenjang === 'SMP') options = ['7','8','9'];
  else if (jenjang === 'SMA') options = ['10','11','12'];

  kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
  options.forEach(k => {
    const opt = document.createElement('option');
    opt.value = k;
    opt.textContent = 'Kelas ' + k;
    kelasSelect.appendChild(opt);
  });
}
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
