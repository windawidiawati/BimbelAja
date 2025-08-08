<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../index.php");
    exit;
}

$siswa_id = $_SESSION['user']['id'];
$latihan_id = $_GET['id'] ?? null;

if (!$latihan_id) {
    header("Location: soal.php");
    exit;
}

// Cek apakah latihan ada dan tenggat belum lewat
$sql = "SELECT * FROM latihan WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $latihan_id);
$stmt->execute();
$latihan = $stmt->get_result()->fetch_assoc();

if (!$latihan) {
    header("Location: soal.php");
    exit;
}

$now = date('Y-m-d H:i:s');
if ($now > $row['tenggat_waktu']) {
    echo "<script>alert('Latihan sudah lewat tenggat waktu!'); window.location='soal.php';</script>";
    exit;
}

// Cek apakah sudah pernah dikerjakan
$sql = "SELECT * FROM jawaban_siswa WHERE latihan_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $latihan_id, $siswa_id);
$stmt->execute();
$cek = $stmt->get_result();

if ($cek->num_rows > 0) {
    header("Location: soal.php?msg=sudah_dikerjakan");
    exit;
}

// Ambil soal-soal
$sql = $sql = "SELECT id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d 
        FROM soal 
        WHERE latihan_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $latihan_id);
$stmt->execute();
$soal = $stmt->get_result();
?>

<div class="container mt-4">
    <h3><?= htmlspecialchars($latihan['judul']) ?></h3>
    <form action="submit_latihan.php" method="POST">
        <input type="hidden" name="latihan_id" value="<?= $latihan_id ?>">

        <?php $no = 1; while ($row = $soal->fetch_assoc()): ?>
    <div class="mb-3">
        <p><strong><?= $no++ ?>. <?= htmlspecialchars($row['pertanyaan']) ?></strong></p>

        <div>
            <input type="radio" name="jawaban[<?= $row['id'] ?>]" value="A">A.
            <?= htmlspecialchars($row['opsi_a']) ?>
        </div>
        <div>
            <input type="radio" name="jawaban[<?= $row['id'] ?>]" value="B">B.
            <?= htmlspecialchars($row['opsi_b']) ?>
        </div>
        <div>
            <input type="radio" name="jawaban[<?= $row['id'] ?>]" value="C">C.
            <?= htmlspecialchars($row['opsi_c']) ?>
        </div>
        <div>
            <input type="radio" name="jawaban[<?= $row['id'] ?>]" value="D">D.
            <?= htmlspecialchars($row['opsi_d']) ?>
        </div>
    </div>
<?php endwhile; ?>



        <button type="submit" class="btn btn-success">Kirim Jawaban</button>
    </form>
</div>
