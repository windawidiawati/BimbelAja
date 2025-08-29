<?php
ob_start();
include '../config/database.php';
include '../includes/admin_header.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
  header("Location: ../index.php");
  exit;
}

// Cek keberadaan kolom created_at / updated_at di tabel users
$has_created_at = false;
$has_updated_at = false;

$colCreated = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'created_at'");
if ($colCreated && mysqli_num_rows($colCreated) > 0) $has_created_at = true;

$colUpdated = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'updated_at'");
if ($colUpdated && mysqli_num_rows($colUpdated) > 0) $has_updated_at = true;

// Hapus user
if (isset($_GET['hapus'])) {
  $id = intval($_GET['hapus']);
  mysqli_query($conn, "DELETE FROM users WHERE id = $id");
  header("Location: kelola_user.php?success=User berhasil dihapus");
  exit;
}

// Tambah user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
  $nama = mysqli_real_escape_string($conn, $_POST['nama'] ?? '');
  $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $role = mysqli_real_escape_string($conn, $_POST['role'] ?? '');
  $jenjang = mysqli_real_escape_string($conn, $_POST['jenjang'] ?? '');
  $kelas = mysqli_real_escape_string($conn, $_POST['kelas'] ?? ''); 
  $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian'] ?? '');
  $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
  $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp'] ?? '');

  if ($nama && $username && $password && $role) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Siapkan bagian created_at bila ada
    $created_part = $has_created_at ? ", created_at" : "";
    $created_values = $has_created_at ? ", NOW()" : "";

    if ($role === 'siswa') {
      $sql = "INSERT INTO users (nama, username, password, role, jenjang, kelas_id, email, no_hp$created_part) VALUES (?, ?, ?, ?, ?, ?, ?, ?$created_values)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssssssss", $nama, $username, $hashed, $role, $jenjang, $kelas, $email, $no_hp);
    } elseif ($role === 'tutor') {
      $sql = "INSERT INTO users (nama, username, password, role, keahlian, email, no_hp$created_part) VALUES (?, ?, ?, ?, ?, ?, ?$created_values)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sssssss", $nama, $username, $hashed, $role, $keahlian, $email, $no_hp);
    } else {
      $sql = "INSERT INTO users (nama, username, password, role, email, no_hp$created_part) VALUES (?, ?, ?, ?, ?, ?$created_values)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssssss", $nama, $username, $hashed, $role, $email, $no_hp);
    }

    if ($stmt && $stmt->execute()) {
      header("Location: kelola_user.php?success=User berhasil ditambahkan");
    } else {
      header("Location: kelola_user.php?error=Gagal menambahkan user");
    }
    exit;
  } else {
    header("Location: kelola_user.php?error=Data tidak lengkap");
    exit;
  }
}

// Update user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
  $id = intval($_POST['id']);
  $nama = mysqli_real_escape_string($conn, $_POST['nama'] ?? '');
  $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $role = mysqli_real_escape_string($conn, $_POST['role'] ?? '');
  $jenjang = mysqli_real_escape_string($conn, $_POST['jenjang'] ?? '');
  $kelas = mysqli_real_escape_string($conn, $_POST['kelas'] ?? '');
  $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian'] ?? '');
  $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
  $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp'] ?? '');

  $timestamp_sql = "";
  if ($has_updated_at) {
    $timestamp_sql = ", updated_at = NOW()";
  } elseif ($has_created_at) {
    $timestamp_sql = ", created_at = NOW()";
  }

  if (empty($password)) {
    if ($role === 'siswa') {
      $sql = "UPDATE users SET nama=?, username=?, role=?, jenjang=?, kelas_id=?, keahlian=NULL, email=?, no_hp=? $timestamp_sql WHERE id=?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sssssssi", $nama, $username, $role, $jenjang, $kelas, $email, $no_hp, $id);
    } elseif ($role === 'tutor') {
      $sql = "UPDATE users SET nama=?, username=?, role=?, keahlian=?, jenjang=NULL, kelas_id=NULL, email=?, no_hp=? $timestamp_sql WHERE id=?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sssssssi", $nama, $username, $role, $keahlian, $email, $no_hp, $id);
    } else {
      $sql = "UPDATE users SET nama=?, username=?, role=?, jenjang=NULL, kelas_id=NULL, keahlian=NULL, email=?, no_hp=? $timestamp_sql WHERE id=?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssssssi", $nama, $username, $role, $email, $no_hp, $id);
    }
  } else {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    if ($role === 'siswa') {
      $sql = "UPDATE users SET nama=?, username=?, password=?, role=?, jenjang=?, kelas_id=?, keahlian=NULL, email=?, no_hp=? $timestamp_sql WHERE id=?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssssssssi", $nama, $username, $hashed, $role, $jenjang, $kelas, $email, $no_hp, $id);
    } elseif ($role === 'tutor') {
      $sql = "UPDATE users SET nama=?, username=?, password=?, role=?, keahlian=?, jenjang=NULL, kelas_id=NULL, email=?, no_hp=? $timestamp_sql WHERE id=?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssssssssi", $nama, $username, $hashed, $role, $keahlian, $email, $no_hp, $id);
    } else {
      $sql = "UPDATE users SET nama=?, username=?, password=?, role=?, jenjang=NULL, kelas_id=NULL, keahlian=NULL, email=?, no_hp=? $timestamp_sql WHERE id=?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sssssssi", $nama, $username, $hashed, $role, $email, $no_hp, $id);
    }
  }

  if ($stmt && $stmt->execute()) {
    header("Location: kelola_user.php?success=User berhasil diperbarui");
  } else {
    header("Location: kelola_user.php?error=Gagal memperbarui user");
  }
  exit;
}

// Query users: urutan paling atas adalah yang baru dibuat / diupdate
$orderBy = "u.id DESC";
if ($has_updated_at || $has_created_at) {
  $orderBy = "COALESCE(u.updated_at, u.created_at, NOW()) DESC, u.id DESC";
}

$sql = "SELECT u.*, k.nama_kelas, p.nama as nama_paket 
        FROM users u 
        LEFT JOIN kelas k ON u.kelas_id = k.id 
        LEFT JOIN langganan l ON u.id = l.user_id 
        LEFT JOIN paket p ON l.paket_id = p.id 
        ORDER BY $orderBy";

$result = mysqli_query($conn, $sql);
$kelas_result = mysqli_query($conn, "SELECT * FROM kelas");
?>

<!-- HTML & JS tetap sama persis (tidak berubah) -->

<?php include '../includes/admin_footer.php'; ?> 


<div class="content">
  <h3>Kelola User</h3>
  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalUser">+ Tambah User</button>

  <div class="table-responsive">
    <table id="userTable" class="table table-bordered bg-white shadow-sm">
      <thead class="table-primary">
        <tr>
          <th>Nama</th>
          <th>Username</th>
          <th>Email</th>
          <th>No HP</th>
          <th>Role</th>
          <th>Jenjang</th>
          <th>Kelas</th>
          <th>Keahlian</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
          <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?= htmlspecialchars($row['nama']) ?></td>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['no_hp']) ?></td>
              <td><?= htmlspecialchars($row['role']) ?></td>
              <td><?= $row['role']==='siswa' ? htmlspecialchars($row['jenjang']) : '-' ?></td>
              <td><?= $row['role']==='siswa' ? htmlspecialchars($row['nama_kelas']) : '-' ?></td>
              <td><?= $row['role']==='tutor' ? htmlspecialchars($row['keahlian']) : '-' ?></td>
              <td>
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditUser" onclick='editUser(<?= json_encode($row) ?>)'>Edit</button>
                <a href="?hapus=<?= intval($row['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
              </td>
            </tr>
          <?php endwhile; ?>
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
        <input type="hidden" name="add_user" value="1">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Tambah User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label>Nama</label><input type="text" name="nama" class="form-control" required></div>
          <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div>
          <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
          <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" placeholder="Masukan email resmi anda"></div>
          <div class="mb-3"><label>No HP</label><input type="text" name="no_hp" class="form-control" maxlength="12"></div>
          <div class="mb-3"><label>Role</label>
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
            <select name="jenjang" class="form-select">
              <option value="">-- Pilih Jenjang --</option>
              <option value="SD">SD</option>
              <option value="SMP">SMP</option>
              <option value="SMA">SMA</option>
            </select>
            <label>Kelas</label>
            <select name="kelas" class="form-select">
              <option value="">-- Pilih Kelas --</option>
              <?php if ($kelas_result) { mysqli_data_seek($kelas_result,0); while($k = mysqli_fetch_assoc($kelas_result)): ?>
                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
              <?php endwhile; } ?>
            </select>
          </div>
          <div id="tutor-fields" style="display: none;">
            <label>Keahlian</label>
            <input type="text" name="keahlian" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
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
      <form method="POST">
        <input type="hidden" name="update_user" value="1">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit-id">
          <div class="mb-3"><label>Nama</label><input type="text" name="nama" id="edit-nama" class="form-control" required></div>
          <div class="mb-3"><label>Username</label><input type="text" name="username" id="edit-username" class="form-control" required></div>
          <div class="mb-3"><label>Password (kosongkan jika tidak diubah)</label><input type="password" name="password" class="form-control"></div>
          <div class="mb-3"><label>Email</label><input type="email" name="email" id="edit-email" class="form-control" placeholder="Masukan email resmi anda"></div>
          <div class="mb-3"><label>No HP</label><input type="text" name="no_hp" id="edit-no_hp" class="form-control" maxlength="12"></div>
          <div class="mb-3"><label>Role</label>
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
            <select name="jenjang" id="edit-jenjang" class="form-select">
              <option value="">-- Pilih Jenjang --</option>
              <option value="SD">SD</option>
              <option value="SMP">SMP</option>
              <option value="SMA">SMA</option>
            </select>
            <label>Kelas</label>
            <select name="kelas" id="edit-kelas" class="form-select">
              <option value="">-- Pilih Kelas --</option>
              <?php if ($kelas_result) { mysqli_data_seek($kelas_result,0); while($k = mysqli_fetch_assoc($kelas_result)): ?>
                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
              <?php endwhile; } ?>
            </select>
          </div>
          <div id="edit-tutor-fields" style="display: none;">
            <label>Keahlian</label>
            <input type="text" name="keahlian" id="edit-keahlian" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- DataTables Scripts -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
  $('#userTable').DataTable({
    "pageLength": 10,
    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
    "ordering": true,
    "responsive": true
  });
});

function handleRoleChange(role) {
  document.getElementById('siswa-fields').style.display = role === 'siswa' ? 'block' : 'none';
  document.getElementById('tutor-fields').style.display = role === 'tutor' ? 'block' : 'none';
}
function editUser(data) {
  document.getElementById('edit-id').value = data.id;
  document.getElementById('edit-nama').value = data.nama;
  document.getElementById('edit-username').value = data.username;
  document.getElementById('edit-email').value = data.email || '';
  document.getElementById('edit-no_hp').value = data.no_hp || '';
  document.getElementById('edit-role').value = data.role;
  handleEditRoleChange(data.role);
  if (data.role === 'siswa') {
    document.getElementById('edit-jenjang').value = data.jenjang || '';
    document.getElementById('edit-kelas').value = data.kelas_id || '';
  }
  if (data.role === 'tutor') {
    document.getElementById('edit-keahlian').value = data.keahlian || '';
  }
}
function handleEditRoleChange(role) {
  document.getElementById('edit-siswa-fields').style.display = role === 'siswa' ? 'block' : 'none';
  document.getElementById('edit-tutor-fields').style.display = role === 'tutor' ? 'block' : 'none';
}
</script>

<?php include '../includes/admin_footer.php'; ?>
