<?php
include '../includes/auth.php';
include '../includes/header.php';

if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php');
  exit;
}

include '../config/database.php';

$tutor_id = $_SESSION['user']['id'];

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
  $opsi_a = mysqli_real_escape_string($conn, $_POST['opsi_a']);
  $opsi_b = mysqli_real_escape_string($conn, $_POST['opsi_b']);
  $opsi_c = mysqli_real_escape_string($conn, $_POST['opsi_c']);
  $opsi_d = mysqli_real_escape_string($conn, $_POST['opsi_d']);
  $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban']);

  $query = "INSERT INTO soal (pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban, tutor_id, created_at)
            VALUES ('$pertanyaan', '$opsi_a', '$opsi_b', '$opsi_c', '$opsi_d', '$jawaban', $tutor_id, NOW())";

  if (mysqli_query($conn, $query)) {
    $success = "Soal berhasil ditambahkan!";
  } else {
    $error = "Gagal menyimpan soal. Silakan coba lagi.";
  }
}

// Ambil semua soal milik tutor
$soal = mysqli_query($conn, "SELECT * FROM soal WHERE tutor_id = $tutor_id ORDER BY created_at DESC");
?>

<div class="container mt-5 mb-5">
  <h3 class="mb-4">Buat Soal Pilihan Ganda</h3>

  <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
  <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

  <form method="POST" class="mb-4">
    <div class="mb-3">
      <label>Pertanyaan</label>
      <textarea name="pertanyaan" class="form-control" required></textarea>
    </div>
    <div class="mb-2">
      <label>Opsi A</label>
      <input type="text" name="opsi_a" class="form-control" required>
    </div>
    <div class="mb-2">
      <label>Opsi B</label>
      <input type="text" name="opsi_b" class="form-control" required>
    </div>
    <div class="mb-2">
      <label>Opsi C</label>
      <input type="text" name="opsi_c" class="form-control" required>
    </div>
    <div class="mb-2">
      <label>Opsi D</label>
      <input type="text" name="opsi_d" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Jawaban Benar (A/B/C/D)</label>
      <input type="text" name="jawaban" class="form-control" maxlength="1" pattern="[A-Da-d]" required>
    </div>
    <button type="submit" class="btn btn-primary">Simpan Soal</button>
  </form>

  <h5>Daftar Soal Anda:</h5>
  <table class="table table-bordered table-striped mt-3">
    <thead class="table-dark">
      <tr>
        <th>Pertanyaan</th>
        <th>A</th>
        <th>B</th>
        <th>C</th>
        <th>D</th>
        <th>Jawaban</th>
        <th>Waktu</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($soal)): ?>
        <tr>
          <td><?= htmlspecialchars($row['pertanyaan']) ?></td>
          <td><?= htmlspecialchars($row['opsi_a']) ?></td>
          <td><?= htmlspecialchars($row['opsi_b']) ?></td>
          <td><?= htmlspecialchars($row['opsi_c']) ?></td>
          <td><?= htmlspecialchars($row['opsi_d']) ?></td>
          <td><strong><?= strtoupper($row['jawaban']) ?></strong></td>
          <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include '../includes/footer.php'; ?>
