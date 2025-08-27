<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../index.php");
    exit;
}

$siswa_id   = $_SESSION['user']['id'];
$latihan_id = $_POST['latihan_id'] ?? null;
$jawaban    = $_POST['jawaban'] ?? [];

if (!$latihan_id || empty($jawaban)) {
    header("Location: soal.php?msg=invalid_data");
    exit;
}

// Ambil soal dan kunci jawaban
$sql = "SELECT id, jawaban FROM soal WHERE latihan_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $latihan_id);
$stmt->execute();
$result = $stmt->get_result();

$total_soal = $result->num_rows;
$benar = 0;

// Simpan jawaban siswa & hitung skor
$sql_insert = "INSERT INTO jawaban_siswa (latihan_id, soal_id, user_id, jawaban) VALUES (?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);

while ($row = $result->fetch_assoc()) {
    $soal_id = $row['id'];
    $kunci   = $row['jawaban'];
    $pilihan = $jawaban[$soal_id] ?? '';

    // Simpan jawaban
    $stmt_insert->bind_param("iiis", $latihan_id, $soal_id, $siswa_id, $pilihan);
    $stmt_insert->execute();

    // Cek benar
    if (strtoupper($pilihan) === strtoupper($kunci)) {
        $benar++;
    }
}
// Hitung nilai
$nilai = $total_soal > 0 ? round(($benar / $total_soal) * 100, 2) : 0;

// UPDATE latihan_siswa jadi selesai + simpan nilai
$sql_update = "UPDATE latihan_siswa 
               SET status = 'selesai', 
                   waktu_selesai = NOW(), 
                   nilai = ? 
               WHERE siswa_id = ? AND latihan_id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("dii", $nilai, $siswa_id, $latihan_id);
$stmt_update->execute();


// Hitung nilai
$nilai = $total_soal > 0 ? round(($benar / $total_soal) * 100, 2) : 0;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Hasil Latihan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Modal -->
<div class="modal fade" id="hasilModal" tabindex="-1" aria-labelledby="hasilModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="hasilModalLabel">Hasil Latihan</h5>
      </div>
      <div class="modal-body">
        <p>Jawaban benar: <strong><?= $benar ?></strong> dari <strong><?= $total_soal ?></strong> soal.</p>
        <p>Nilai: <strong><?= $nilai ?></strong></p>
      </div>
      <div class="modal-footer">
        <a href="soal.php" class="btn btn-primary">Kembali</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Tampilkan modal otomatis
    var myModal = new bootstrap.Modal(document.getElementById('hasilModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
</script>
</body>
</html>
