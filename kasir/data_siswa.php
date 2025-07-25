<?php
include '../config/database.php';
include '../includes/auth.php';

// Pastikan hanya kasir yang bisa akses
if ($_SESSION['user']['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

// Ambil data kelas dari database
$kelas_result = mysqli_query($conn, "SELECT nama_kelas FROM kelas ORDER BY nama_kelas ASC");

// Proses Tambah Siswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_siswa'])) {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $kelas    = trim($_POST['kelas']);
    $jenjang  = trim($_POST['jenjang']);
    $email    = trim($_POST['email']);
    $no_hp    = trim($_POST['no_hp']);
    $status   = $_POST['status'] ?? 'belum_aktif';

    if ($nama && $username && $kelas && $jenjang) {
        $stmt = $conn->prepare("
            INSERT INTO users (nama, username, password, role, kelas, jenjang, email, no_hp, status) 
            VALUES (?, ?, ?, 'siswa', ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssssss", $nama, $username, $password, $kelas, $jenjang, $email, $no_hp, $status);
        $stmt->execute();
        $stmt->close();
        header("Location: data_siswa.php");
        exit;
    }
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $hapus_id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM users WHERE id=$hapus_id AND role='siswa'");
    header("Location: data_siswa.php");
    exit;
}

// Proses Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $edit_id  = intval($_POST['edit_id']);
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $kelas    = trim($_POST['kelas']);
    $jenjang  = trim($_POST['jenjang']);
    $email    = trim($_POST['email']);
    $no_hp    = trim($_POST['no_hp']);
    $status   = $_POST['status'] ?? 'belum_aktif';

    $update = $conn->prepare("
        UPDATE users SET nama=?, username=?, kelas=?, jenjang=?, email=?, no_hp=?, status=? 
        WHERE id=? AND role='siswa'
    ");
    $update->bind_param("sssssssi", $nama, $username, $kelas, $jenjang, $email, $no_hp, $status, $edit_id);
    $update->execute();
    $update->close();

    header("Location: data_siswa.php");
    exit;
}

// Ambil data siswa
$siswa_result = mysqli_query($conn, "SELECT * FROM users WHERE role='siswa'");

include '../includes/kasir_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <h4 class="fw-bold"><i class="bi bi-people me-2"></i>Data Siswa</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahSiswaModal">
            <i class="bi bi-person-plus me-1"></i> Tambah Siswa
        </button>
    </div>

    <div class="card shadow border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th>Kelas</th>
                            <th>Jenjang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($s = mysqli_fetch_assoc($siswa_result)) {
                            $user_id = $s['id'];
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($s['nama']) ?></td>
                            <td><?= htmlspecialchars($s['username']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td><?= htmlspecialchars($s['no_hp']) ?></td>
                            <td><?= htmlspecialchars($s['kelas']) ?></td>
                            <td><?= htmlspecialchars($s['jenjang']) ?></td>
                            <td>
                                <span class="badge <?= $s['status'] == 'aktif' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($s['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $user_id ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="data_siswa.php?hapus=<?= $user_id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus siswa ini?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Edit (masih tetap input manual, bisa diupgrade nanti) -->
                        <div class="modal fade" id="editModal<?= $user_id ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Siswa</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="edit_id" value="<?= $user_id ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Nama</label>
                                                <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($s['nama']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Username</label>
                                                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($s['username']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($s['email']) ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">No HP</label>
                                                <input type="text" class="form-control" name="no_hp" value="<?= htmlspecialchars($s['no_hp']) ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Kelas</label>
                                                <input type="text" class="form-control" name="kelas" value="<?= htmlspecialchars($s['kelas']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Jenjang</label>
                                                <select class="form-select" name="jenjang" required>
                                                    <option value="SD" <?= $s['jenjang']=='SD'?'selected':'' ?>>SD</option>
                                                    <option value="SMP" <?= $s['jenjang']=='SMP'?'selected':'' ?>>SMP</option>
                                                    <option value="SMA IPA" <?= $s['jenjang']=='SMA IPA'?'selected':'' ?>>SMA IPA</option>
                                                    <option value="SMA IPS" <?= $s['jenjang']=='SMA IPS'?'selected':'' ?>>SMA IPS</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" name="status">
                                                    <option value="aktif" <?= $s['status']=='aktif'?'selected':'' ?>>Aktif</option>
                                                    <option value="belum_aktif" <?= $s['status']=='belum_aktif'?'selected':'' ?>>Belum Aktif</option>
                                                </select>
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
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Siswa -->
<div class="modal fade" id="tambahSiswaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="tambah_siswa" value="1">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No HP</label>
                        <input type="text" name="no_hp" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select name="kelas" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php while ($k = mysqli_fetch_assoc($kelas_result)) : ?>
                                <option value="<?= htmlspecialchars($k['nama_kelas']) ?>">
                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenjang</label>
                        <select name="jenjang" class="form-select" required>
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA IPA">SMA IPA</option>
                            <option value="SMA IPS">SMA IPS</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="aktif">Aktif</option>
                            <option value="belum_aktif" selected>Belum Aktif</option>
                        </select>
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

<?php include '../includes/kasir_footer.php'; ?>
