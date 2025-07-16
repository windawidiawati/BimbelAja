<?php
include '../includes/auth.php';
include '../includes/header.php';
include '../config/database.php';

$user_id = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'];
$role = $_SESSION['user']['role'];

// Cek langganan
$query = "SELECT paket FROM langganan WHERE username = '$username' AND status = 'aktif' LIMIT 1";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$paket = $row['paket'] ?? 'none';

// Hanya pengguna premium yang bisa akses forum
if ($paket !== 'premium') {
?>
  <div class="container mt-5">
    <div class="alert alert-warning text-center">
      <h4><i class="bi bi-lock-fill"></i> Forum Diskusi Terkunci</h4>
      <p>Fitur forum hanya tersedia untuk pengguna dengan <strong>paket Premium</strong>.</p>
      <a href="../langganan/paket.php" class="btn btn-primary mt-2">Upgrade ke Premium</a>
    </div>
  </div>
<?php
include '../includes/footer.php';
exit;
}

// ========== Jika Premium, Lanjut Menampilkan Forum ==========

// Proses kirim pesan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $judul = mysqli_real_escape_string($conn, $_POST['judul']);
  $isi = mysqli_real_escape_string($conn, $_POST['isi']);
  $parent_id = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : NULL;

  if (!empty($isi)) {
    $stmt = $conn->prepare("INSERT INTO forum (parent_id, judul, isi, user_id, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issis", $parent_id, $judul, $isi, $user_id, $role);
    $stmt->execute();
    $stmt->close();
  }
}

// Ambil semua topik utama
$topik = mysqli_query($conn, "SELECT * FROM forum WHERE parent_id IS NULL ORDER BY created_at DESC");
?>

<div class="container mt-5">
  <h3 class="fw-bold">Forum Diskusi Siswa & Tutor</h3>

  <!-- Form Buat Topik Baru -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">Buat Topik Baru</div>
    <div class="card-body">
      <form action="" method="POST">
        <input type="hidden" name="parent_id" value="">
        <div class="mb-3">
          <label for="judul" class="form-label">Judul</label>
          <input type="text" class="form-control" name="judul" required>
        </div>
        <div class="mb-3">
          <label for="isi" class="form-label">Pesan</label>
          <textarea class="form-control" name="isi" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-success"><i class="bi bi-send"></i> Kirim</button>
      </form>
    </div>
  </div>

  <!-- Daftar Topik dan Diskusi -->
  <?php while ($row = mysqli_fetch_assoc($topik)): ?>
    <div class="card mb-3 shadow-sm">
      <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($row['judul']) ?></h5>
        <p class="card-text"><?= nl2br(htmlspecialchars($row['isi'])) ?></p>
        <div class="small text-muted">Oleh: <?= $row['role'] ?> - <?= date('d M Y H:i', strtotime($row['created_at'])) ?></div>
        <hr>

        <!-- Balasan -->
        <?php
        $id_topik = $row['id'];
        $balasan = mysqli_query($conn, "SELECT * FROM forum WHERE parent_id = $id_topik ORDER BY created_at ASC");
        ?>
        <div class="ps-3">
          <?php while ($reply = mysqli_fetch_assoc($balasan)): ?>
            <div class="border-start ps-3 mb-2">
              <p><?= nl2br(htmlspecialchars($reply['isi'])) ?></p>
              <div class="small text-muted">Balasan oleh: <?= $reply['role'] ?> - <?= date('d M Y H:i', strtotime($reply['created_at'])) ?></div>
            </div>
          <?php endwhile; ?>
        </div>

        <!-- Form Balas -->
        <form action="" method="POST" class="mt-3">
          <input type="hidden" name="parent_id" value="<?= $row['id'] ?>">
          <input type="hidden" name="judul" value="<?= htmlspecialchars($row['judul']) ?>">
          <div class="mb-2">
            <textarea name="isi" rows="2" class="form-control" placeholder="Tulis balasan..." required></textarea>
          </div>
          <button type="submit" class="btn btn-outline-primary btn-sm">Balas</button>
        </form>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<?php include '../includes/footer.php'; ?>
