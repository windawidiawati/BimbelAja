<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php');
  exit;
}
include '../includes/header.php';
include '../config/database.php';

$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

// Menyimpan diskusi baru atau balasan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $judul = mysqli_real_escape_string($conn, $_POST['judul']);
  $isi = mysqli_real_escape_string($conn, $_POST['isi']);
  $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 'NULL';

  $query = "INSERT INTO forum (judul, isi, user_id, role, parent_id, created_at) 
            VALUES ('$judul', '$isi', $user_id, '$role', $parent_id, NOW())";
  mysqli_query($conn, $query);
}

// Ambil semua diskusi utama (bukan balasan)
$diskusi = mysqli_query($conn, "SELECT * FROM forum WHERE parent_id IS NULL ORDER BY created_at DESC");
?>

<div class="container mt-5 mb-5">
  <h3>Forum Diskusi</h3>

  <!-- Form buat diskusi baru -->
  <form method="POST" class="mb-4 p-4 border rounded bg-light">
    <h5>Buat Diskusi Baru</h5>
    <div class="mb-2">
      <input type="text" name="judul" class="form-control" placeholder="Judul Diskusi" required>
    </div>
    <div class="mb-2">
      <textarea name="isi" class="form-control" placeholder="Tulis isi diskusi..." required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Kirim</button>
  </form>

  <!-- Daftar Diskusi -->
  <?php while ($row = mysqli_fetch_assoc($diskusi)): ?>
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($row['judul']); ?></h5>
        <p class="card-text"><?= nl2br(htmlspecialchars($row['isi'])); ?></p>
        <p class="card-text">
          <small class="text-muted">Oleh <?= $row['role']; ?> - <?= date('d M Y H:i', strtotime($row['created_at'])); ?></small>
        </p>

        <!-- Form balasan -->
        <form method="POST" class="mb-2">
          <input type="hidden" name="parent_id" value="<?= $row['id']; ?>">
          <input type="hidden" name="judul" value="Re: <?= htmlspecialchars($row['judul']); ?>">
          <textarea name="isi" class="form-control mb-2" placeholder="Tulis balasan..." required></textarea>
          <button type="submit" class="btn btn-sm btn-secondary">Balas</button>
        </form>

        <!-- Balasan -->
        <?php
        $id = $row['id'];
        $balasan = mysqli_query($conn, "SELECT * FROM forum WHERE parent_id = $id ORDER BY created_at ASC");
        while ($b = mysqli_fetch_assoc($balasan)):
        ?>
          <div class="border rounded p-2 mb-2 ms-4 bg-light">
            <p class="mb-1"><strong><?= $b['role']; ?>:</strong> <?= nl2br(htmlspecialchars($b['isi'])); ?></p>
            <small class="text-muted"><?= date('d M Y H:i', strtotime($b['created_at'])); ?></small>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<?php include '../includes/footer.php'; ?>
