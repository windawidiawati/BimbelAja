<?php
include '../includes/auth.php';
include '../includes/header.php';

if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php');
  exit;
}

include '../config/database.php';

$tutor_id = $_SESSION['user']['id'];

// Ambil jawaban siswa untuk soal milik tutor
$query = "
  SELECT 
    u.username AS nama_siswa,
    s.pertanyaan,
    js.jawaban_dipilih,
    s.jawaban AS jawaban_benar,
    js.skor,
    js.created_at
  FROM jawaban_siswa js
  JOIN users u ON js.siswa_id = u.id
  JOIN soal s ON js.soal_id = s.id
  WHERE s.tutor_id = $tutor_id
  ORDER BY js.created_at DESC
";
$result = mysqli_query($conn, $query);
?>

<div class="container mt-5 mb-5">
  <h3 class="mb-4">Lihat Nilai Siswa</h3>

  <?php if (mysqli_num_rows($result) > 0): ?>
    <table class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th>Nama Siswa</th>
          <th>Pertanyaan</th>
          <th>Jawaban Dipilih</th>
          <th>Jawaban Benar</th>
          <th>Skor</th>
          <th>Waktu</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
            <td><?= htmlspecialchars($row['pertanyaan']) ?></td>
            <td><?= strtoupper(htmlspecialchars($row['jawaban_dipilih'])) ?></td>
            <td><?= strtoupper(htmlspecialchars($row['jawaban_benar'])) ?></td>
            <td><?= (int)$row['skor'] ?></td>
            <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-info">Belum ada siswa yang mengerjakan soal.</div>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
