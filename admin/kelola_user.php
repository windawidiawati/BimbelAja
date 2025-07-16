<<<<<<< HEAD
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola User BimbelAja</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php
    // Koneksi ke database
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'bimbelaja';
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Fungsi untuk mendapatkan semua user
    function getUsers($conn) {
        $query = "SELECT * FROM users ORDER BY created_at DESC";
        $result = $conn->query($query);
        
        $users = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        return $users;
    }
=======
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
>>>>>>> origin

    // Menangani aksi tambah, edit, dan hapus
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['add_user'])) {
            // Tambah user
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role = $_POST['role'];
            $nama = $_POST['nama'];
            $keahlian = $_POST['keahlian'];
            $kelas = $_POST['kelas'];
            $jenjang = $_POST['jenjang'];

            $stmt = $conn->prepare("INSERT INTO users (username, password, role, nama, keahlian, kelas, jenjang) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $username, $password, $role, $nama, $keahlian, $kelas, $jenjang);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['edit_user'])) {
            // Edit user
            $id = $_POST['id'];
            $username = $_POST['username'];
            $role = $_POST['role'];
            $nama = $_POST['nama'];
            $keahlian = $_POST['keahlian'];
            $kelas = $_POST['kelas'];
            $jenjang = $_POST['jenjang'];

            $stmt = $conn->prepare("UPDATE users SET username=?, role=?, nama=?, keahlian=?, kelas=?, jenjang=? WHERE id=?");
            $stmt->bind_param("ssssssi", $username, $role, $nama, $keahlian, $kelas, $jenjang, $id);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['delete_user'])) {
            // Hapus user
            $id = $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    $users = getUsers($conn);
    ?>

    <div class="min-h-screen">
        <!-- Header Admin -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-800">Kelola User BimbelAja</h1>
                <button onclick="document.getElementById('addUser Modal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Tambah User</button>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="min-w-full">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $user['id'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['role']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['nama']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button onclick="editUser (<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>', '<?= htmlspecialchars($user['role']) ?>', '<?= htmlspecialchars($user['nama']) ?>', '<?= htmlspecialchars($user['keahlian']) ?>', '<?= htmlspecialchars($user['kelas']) ?>', '<?= htmlspecialchars($user['jenjang']) ?>')" class="text-yellow-600 hover:text-yellow-800">Edit</button>
                                <form action="" method="POST" class="inline">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="delete_user" class="text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Tambah User -->
    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50" id="addUser Modal">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-bold text-gray-800">Tambah User Baru</h3>
                <button onclick="document.getElementById('addUser Modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="" method="POST" class="p-6 space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                    <select name="role" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="siswa">Siswa</option>
                        <option value="tutor">Tutor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" name="nama" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="keahlian" class="block text-sm font-medium text-gray-700">Keahlian</label>
                    <input type="text" name="keahlian" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="kelas" class="block text-sm font-medium text-gray-700">Kelas</label>
                    <input type="text" name="kelas" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="jenjang" class="block text-sm font-medium text-gray-700">Jenjang</label>
                    <input type="text" name="jenjang" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <button type="submit" name="add_user" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50" id="editUser Modal">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-bold text-gray-800">Edit User</h3>
                <button onclick="document.getElementById('editUser Modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="id" id="editUser Id">
                <div>
                    <label for="editUsername" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" id="editUsername" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label for="editRole" class="block text-sm font-medium text-gray-700">Role</label>
                    <select name="role" id="editRole" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="siswa">Siswa</option>
                        <option value="tutor">Tutor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label for="editNama" class="block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" name="nama" id="editNama" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="editKeahlian" class="block text-sm font-medium text-gray-700">Keahlian</label>
                    <input type="text" name="keahlian" id="editKeahlian" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="editKelas" class="block text-sm font-medium text-gray-700">Kelas</label>
                    <input type="text" name="kelas" id="editKelas" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="editJenjang" class="block text-sm font-medium text-gray-700">Jenjang</label>
                    <input type="text" name="jenjang" id="editJenjang" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <button type="submit" name="edit_user" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg">Simpan</button>
            </form>
        </div>
    </div>

    <script>
        function editUser (id, username, role, nama, keahlian, kelas, jenjang) {
            document.getElementById('editUser Id').value = id;
            document.getElementById('editUsername').value = username;
            document.getElementById('editRole').value = role;
            document.getElementById('editNama').value = nama;
            document.getElementById('editKeahlian').value = keahlian;
            document.getElementById('editKelas').value = kelas;
            document.getElementById('editJenjang').value = jenjang;
            document.getElementById('editUser Modal').classList.remove('hidden');
        }
    </script>
</body>
</html>
