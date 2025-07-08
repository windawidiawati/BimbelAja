<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'siswa') {
  header('Location: ../index.php'); exit;
}
include '../includes/header.php';
include '../config/database.php';

// Ambil data materi dari database
$query = "SELECT * FROM materi";
$result = $conn->query($query);
?>

<div class="container mt-5">
  <h3>Materi Pembelajaran</h3>
  <p>Berikut ini adalah materi dari tutor:</p>

  <?php if ($result->num_rows > 0): ?>
    <div class="row">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($row['judul']) ?></h5>
              <p class="card-text"><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></p>
            </div>
            <div class="card-footer">
              <?php if ($row['tipe_file'] === 'video'): ?>
                <a href="../uploads/<?= $row['file'] ?>" class="btn btn-primary" target="_blank">Tonton Video</a>
              <?php else: ?>
                <a href="../uploads/<?= $row['file'] ?>" class="btn btn-success" download>Download PDF</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-warning">Belum ada materi tersedia.</div>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
