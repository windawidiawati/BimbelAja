<?php
ob_start();
include '../config/database.php';
include '../includes/admin_header.php';

if ($_SESSION['user']['role'] !== 'admin') {
  header("Location: ../index.php");
  exit;
}

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

  if ($nama && $username && $password && $role) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    
    if ($role === 'siswa') {
      $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role, jenjang, kelas_id) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("sssssi", $nama, $username, $hashed, $role, $jenjang, $kelas);
    } elseif ($role === 'tutor') {
      $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role, keahlian) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("sssss", $nama, $username, $hashed, $role, $keahlian);
    } else {
      $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $nama, $username, $hashed, $role);
    }

    if ($stmt->execute()) {
      header("Location: kelola_user.php?success=User berhasil ditambahkan");
    } else {
      header("Location: kelola_user.php?error=Gagal menambahkan user");
    }
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

  // Update data tanpa password jika kosong
  if (empty($password)) {
    if ($role === 'siswa') {
      $stmt = $conn->prepare("UPDATE users SET nama=?, username=?, role=?, jenjang=?, kelas_id=?, keahlian=NULL WHERE id=?");
      $stmt->bind_param("sssssi", $nama, $username, $role, $jenjang, $kelas, $id);
    } elseif ($role === 'tutor') {
      $stmt = $conn->prepare("UPDATE users SET nama=?, username=?, role=?, keahlian=?, jenjang=NULL, kelas_id=NULL WHERE id=?");
      $stmt->bind_param("ssssi", $nama, $username, $role, $keahlian, $id);
    } else {
      $stmt = $conn->prepare("UPDATE users SET nama=?, username=?, role=?, jenjang=NULL, kelas_id=NULL, keahlian=NULL WHERE id=?");
      $stmt->bind_param("sssi", $nama, $username, $role, $id);
    }
  } else {
    // Update data dengan password jika diisi
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    if ($role === 'siswa') {
      $stmt = $conn->prepare("UPDATE users SET nama=?, username=?, password=?, role=?, jenjang=?, kelas_id=?, keahlian=NULL WHERE id=?");
      $stmt->bind_param("ssssssi", $nama, $username, $hashed, $role, $jenjang, $kelas, $id);
    } elseif ($role === 'tutor') {
      $stmt = $conn->prepare("UPDATE users SET nama=?, username=?, password=?, role=?, keahlian=?, jenjang=NULL, kelas_id=NULL WHERE id=?");
      $stmt->bind_param("sssssi", $nama, $username, $hashed, $role, $keahlian, $id);
    } else {
      $stmt = $conn->prepare("UPDATE users SET nama=?, username=?, password=?, role=?, jenjang=NULL, kelas_id=NULL, keahlian=NULL WHERE id=?");
      $stmt->bind_param("ssssi", $nama, $username, $hashed, $role, $id);
    }
  }

  if ($stmt->execute()) {
    header("Location: kelola_user.php?success=User berhasil diperbarui");
  } else {
    header("Location: kelola_user.php?error=Gagal memperbarui user");
  }
  exit;
}

// Filter
$filter_role = $_GET['role'] ?? '';
$filter_jenjang = $_GET['jenjang'] ?? '';
$filter_kelas = $_GET['kelas'] ?? '';
$filter_keahlian = $_GET['keahlian'] ?? '';

// Detail user
$detail_user = null;
if (isset($_GET['detail'])) {
  $id = intval($_GET['detail']);
  $detail_user = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT u.*, k.nama_kelas, p.nama as nama_paket 
     FROM users u 
     LEFT JOIN kelas k ON u.kelas_id = k.id 
     LEFT JOIN langganan l ON u.id = l.user_id 
     LEFT JOIN paket p ON l.paket_id = p.id 
     WHERE u.id = $id"));
}

// Query users dengan join untuk mendapatkan data paket
$sql = "SELECT u.*, k.nama_kelas, p.nama as nama_paket 
        FROM users u 
        LEFT JOIN kelas k ON u.kelas_id = k.id 
        LEFT JOIN langganan l ON u.id = l.user_id 
        LEFT JOIN paket p ON l.paket_id = p.id 
        WHERE 1=1";

if ($filter_role) {
  $sql .= " AND u.role = '$filter_role'";
  if ($filter_role === 'siswa' && $filter_jenjang) {
    $sql .= " AND u.jenjang = '$filter_jenjang'";
    if ($filter_kelas) {
      $sql .= " AND u.kelas_id = '$filter_kelas'";
    }
  }
  if ($filter_role === 'tutor' && $filter_keahlian) {
    $sql .= " AND u.keahlian LIKE '%$filter_keahlian%'";
  }
}
$sql .= " ORDER BY u.nama ASC";
$result = mysqli_query($conn, $sql);

// Ambil data kelas untuk dropdown
$kelas_result = mysqli_query($conn, "SELECT * FROM kelas");
?>

<div class="content">
  <!-- Tampilkan pesan sukses/error -->
  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
  <?php endif; ?>

  <!-- Tampilan Detail User -->
  <?php if ($detail_user): ?>
    <div class="card mb-4">
      <div class="card-header bg-white shadow-sm  d-flex justify-content-between">
        <h4>Detail Siswa: <?= htmlspecialchars($detail_user['nama']) ?></h4>
        <a href="kelola_user.php" class="btn btn-light btn-sm">Kembali</a>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <h5>Informasi Siswa</h5>
          <div class="row">
            <div class="col-md-6">
              <p><strong>Username:</strong> <?= htmlspecialchars($detail_user['username']) ?></p>
              <p><strong>Jenjang:</strong> <?= htmlspecialchars($detail_user['jenjang']) ?> | 
                 <strong>Kelas:</strong> <?= htmlspecialchars($detail_user['nama_kelas'] ?? '-') ?></p>
            </div>
          </div>
        </div>

        <hr>

        <div class="mb-4">
          <h5>Riwayat Pembayaran</h5>
          <div class="table-responsive mb-5">
          <table class="table table-bordered bg-white shadow-sm">
        <thead class="table-primary">
                <tr>
                  <th>No</th>
                  <th>Paket</th>
                  <th>Harga</th>
                  <th>Status</th>
                  <th>Tanggal</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1</td>
                  <td><?= htmlspecialchars($detail_user['nama_paket'] ?? 'Paket Premium SMP Kelas 7') ?></td>
                  <td>Rp 500.000</td>
                  <td>Lunas</td>
                  <td>05 Aug 2025 00:00</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      

  <?php else: ?>
    <!-- Tampilan Utama Kelola User -->
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
            <?php while($kelas = mysqli_fetch_assoc($kelas_result)): ?>
              <option value="<?= $kelas['id'] ?>" <?= $filter_kelas == $kelas['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($kelas['nama_kelas']) ?>
              </option>
            <?php endwhile; ?>
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
     <table class="table table-bordered bg-white shadow-sm">
        <thead class="table-primary">
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
            <td><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['username'] ?? '-') ?></td>
            <td><?= $row['role'] ?? '-' ?></td>
            <td><?= ($row['role'] === 'siswa') ? ($row['jenjang'] ?? '-') : '-' ?></td>
            <td><?= ($row['role'] === 'siswa') ? ($row['nama_kelas'] ?? '-') : '-' ?></td>
            <td><?= ($row['role'] === 'tutor') ? ($row['keahlian'] ?? '-') : '-' ?></td>
            <td>
              <?php if ($row['role'] === 'siswa'): ?>
                <a href="?detail=<?= intval($row['id']) ?>" class="btn btn-sm btn-info">Detail</a>
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditUser" onclick='editUser(<?= json_encode($row) ?>)'>Edit</button>
                <a href="?hapus=<?= intval($row['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
              <?php elseif (in_array($row['role'], ['tutor', 'admin', 'kasir'])): ?>
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditUser" onclick='editUser(<?= json_encode($row) ?>)'>Edit</button>
                <a href="?hapus=<?= intval($row['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
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
  <?php endif; ?>
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
            <select name="jenjang" id="jenjang" class="form-select">
              <option value="">-- Pilih Jenjang --</option>
              <option value="SD">SD</option>
              <option value="SMP">SMP</option>
              <option value="SMA">SMA</option>
            </select>
            <label class="mt-2">Kelas</label>
            <select name="kelas" id="kelas" class="form-select">
              <option value="">-- Pilih Kelas --</option>
              <?php 
                mysqli_data_seek($kelas_result, 0); // Reset pointer
                while($kelas = mysqli_fetch_assoc($kelas_result)): 
              ?>
                <option value="<?= $kelas['id'] ?>"><?= htmlspecialchars($kelas['nama_kelas']) ?></option>
              <?php endwhile; ?>
            </select>
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
            <select name="jenjang" id="edit-jenjang" class="form-select">
              <option value="">-- Pilih Jenjang --</option>
              <option value="SD">SD</option>
              <option value="SMP">SMP</option>
              <option value="SMA">SMA</option>
            </select>
            <label class="mt-2">Kelas</label>
            <select name="kelas" id="edit-kelas" class="form-select">
              <option value="">-- Pilih Kelas --</option>
              <?php 
                mysqli_data_seek($kelas_result, 0); // Reset pointer
                while($kelas = mysqli_fetch_assoc($kelas_result)): 
              ?>
                <option value="<?= $kelas['id'] ?>"><?= htmlspecialchars($kelas['nama_kelas']) ?></option>
              <?php endwhile; ?>
            </select>
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

<script>
function handleRoleChange(role) {
  document.getElementById('siswa-fields').style.display = role === 'siswa' ? 'block' : 'none';
  document.getElementById('tutor-fields').style.display = role === 'tutor' ? 'block' : 'none';
}

function editUser(data) {
  document.getElementById('edit-id').value = data.id;
  document.getElementById('edit-nama').value = data.nama;
  document.getElementById('edit-username').value = data.username;
  document.getElementById('edit-role').value = data.role;
  handleEditRoleChange(data.role);

  if (data.role === 'siswa') {
    document.getElementById('edit-jenjang').value = data.jenjang || '';
    document.getElementById('edit-kelas').value = data.kelas_id || '';
    document.getElementById('edit-siswa-fields').style.display = 'block';
  } else {
    document.getElementById('edit-siswa-fields').style.display = 'none';
  }

  if (data.role === 'tutor') {
    document.getElementById('edit-keahlian').value = data.keahlian || '';
    document.getElementById('edit-tutor-fields').style.display = 'block';
  } else {
    document.getElementById('edit-tutor-fields').style.display = 'none';
  }
}

function handleEditRoleChange(role) {
  document.getElementById('edit-siswa-fields').style.display = role === 'siswa' ? 'block' : 'none';
  document.getElementById('edit-tutor-fields').style.display = role === 'tutor' ? 'block' : 'none';
}
</script>

<?php include '../includes/admin_footer.php'; ?>